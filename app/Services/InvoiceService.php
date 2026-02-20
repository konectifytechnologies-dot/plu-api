<?php 
namespace App\Services;

use App\Factories\InvoiceFactory;
use App\Http\Resources\TenantResource;
use App\Models\AdditionalCost;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Property;
use App\Models\Tenancy;
use App\Models\Unit;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceService
{
    public function generate(Property $property): string
    {
        return DB::transaction(function () use ($property) {

            // Lock the row to prevent race conditions
            $property = Property::where('id', $property->id)
                ->lockForUpdate()
                ->first();

            // Increment counter
            $property->invoice_counter += 1;
            $property->save();

            // Generate initials
            $initials = $this->generateInitials($property->name);

            // Pad number (001, 002, etc)
            $number = str_pad($property->invoice_counter, 3, '0', STR_PAD_LEFT);

            return "{$initials}-INV{$number}";
        });
    }

    public function generateInitials(string $name): string
    {
        $words = explode(' ', $name);

        $initials = collect($words)
            ->map(fn($word) => strtoupper(Str::substr($word, 0, 1)))
            ->implode('');

        return $initials;
    }

    public function createInvoice(Property $property, Unit $unit, bool $isFirst)
    {
        $invoice = null;
        DB::transaction(function () use ($property, &$invoice, $isFirst, $unit) {
            $invoiceNumber = $this->generate($property);
            $invoiceid = Str::ulid();
            $today = Carbon::today();
            $dueDate = Carbon::create($today->year,$today->month,$property->rent_due_date);
            if ($today->greaterThan($dueDate)) {
                $dueDate->addMonth();
            }
            $costs = AdditionalCost::where('property_id', $property->id)->get();
            $invoice = Invoice::insert([
                'id'=>$invoiceid,
                'property_id'=>$property->id,
                'unit_id'=>$unit->id,
                'invoice_number'=>$invoiceNumber,
                'status'=>'pending',
                'invoice_url'=>'https://pludevelopers.co.ke/invoice/'.$invoiceid,
                'due_date'=> $isFirst ? $today : $dueDate,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
            $date = Carbon::parse($dueDate);
            $invoiceItems = [];
            $invoiceItems[] = [
                'id'=>Str::ulid(),
                'invoice_id'=>$invoiceid,
                'item_name'=>'Rent',
                'description'=>$isFirst ? 'First Month Rent' : 'Rent For'.$date->format('F Y'),
                'amount'=>$unit->rent,
                'quantity'=>1,
                'total'=>$unit->rent
            ];
            if($isFirst && $property->deposit_required){
                $invoiceItems[] = [
                    'id'=>Str::ulid(),
                    'invoice_id'=>$invoiceid,
                    'item_name'=>'Deposit',
                    'description'=>'1 Month Deposit',
                    'amount'=>$unit->rent,
                    'quantity'=>1,
                    'total'=>$unit->rent
                ];
            }
            InvoiceItem::insert($invoiceItems);



        });

        return $invoice;

    }

    public function generateTenantInvoices()
    {
        $invoices = [];
        DB::transaction(function () use (&$invoices) {
            $factory = app(InvoiceFactory::class);
            $invoiceArray = [];
            $invoiceItems = [];
             Tenancy::with([
                        'user' => fn ($q) => $q->where('is_deleted', false),
                        'property.costs',
                        'unit:id,name,rent'
                    ])
                    ->where('status', 'active')
                    ->chunkById(100, function ($tenancies) use (&$invoiceArray, &$invoices, &$invoiceItems, $factory) {

                        foreach ($tenancies as $tenancy) {
                            $invoiceid = Str::ulid();
                            $today = Carbon::today();
                            $dueDate = Carbon::create($today->year,$today->month,$tenancy->property->rent_due_date);
                            if ($today->greaterThan($dueDate)) {
                                $dueDate->addMonth();
                            }
                            $date = Carbon::parse($dueDate);
                            $invoicenumber =  $this->generate($tenancy->property);
                            $invoiceurl = 'https://pludevelopers.co.ke/invoice/'.$invoiceid;
                            $invoiceArray[] = $factory->makeInvoice(
                                $invoicenumber,
                                $invoiceid,
                                $tenancy->property->id,
                                $tenancy->unit->id,
                                $date->toDateString(),
                                [
                                    'name'=>$tenancy->user->name,
                                    'email'=>$tenancy->user->email,
                                    'number'=>$tenancy->user->number,
                                    'due_date'=>$tenancy->property->rent_due_date,
                                    'message'=>'Your Rent for'.' '.$date->format('F Y').' '.'Will be due on'.' '. $dueDate->format('d F Y').'. '.'To Pay your rent click on the pay button or Link below',
                                    'url'=>$invoiceurl,
                                    'invoice_number'=>$invoicenumber,
                                    'property_name'=>$tenancy->property->name
                                ]
                            );
                            $invoiceItems[] = $factory->makeInvoiceItems(
                                Str::ulid(),
                                $invoiceid,
                                'Rent',
                                'Rent For'.$date->format('F Y'),
                                $tenancy->unit->rent
                            );
                            $costs = $tenancy->property->costs ?? [];
                            if(!empty($costs)){
                                foreach ($costs as $cost){
                                    $invoiceItems[] = $factory->makeInvoiceItems(
                                        Str::ulid(),
                                        $invoiceid,
                                        $cost->title,
                                        $cost->title,
                                        $cost->cost
                                    );
                                }
                            }
                            $invoices = $invoiceArray;
                            Invoice::insert($invoiceArray);
                            InvoiceItem::insert($invoiceItems);
                            

                            
                        }

                    });

        });
       
        
        return $invoices;
        
    }
} 