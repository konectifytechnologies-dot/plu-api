<?php

namespace App\Factories;

use App\Services\InvoiceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InvoiceFactory 
{
    protected InvoiceService $invoice;
    public function __construct(InvoiceService $invoice)
    {
        $this->invoice = $invoice;
    } 
    public function makeInvoice(string $invoice_number, string $invoiceid, string $property_id, string $unit_id, $duedate, array $data)
    {
        $timestamp = now();

        return [
            'id'=>$invoiceid,
            'property_id'=>$property_id,
            'unit_id'=>$unit_id,
            'invoice_number'=>$invoice_number,
            'status'=>'pending',
            'invoice_url'=>'https://pludevelopers.co.ke/invoice/'.$invoiceid,
            'due_date'=> $duedate,
            'created_at'=>$timestamp,
            'updated_at'=>$timestamp,
            'invoice_reminder_data'=>json_encode($data)
        ];

    }

    public function makeInvoiceItems(string $id, string $invoiceid, string $item_name, string $description, int $amount )
    {
        $timestamp = now();

        return [
           'id'=>Str::ulid(),
            'invoice_id'=>$invoiceid,
            'item_name'=>$item_name,
            'description'=>$description,
            'amount'=>$amount,
            'quantity'=>1,
            'total'=>$amount * 1,
            'created_at'=>$timestamp,
            'updated_at'=>$timestamp,
        ];
    }
}
