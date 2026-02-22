<?php

namespace App\Http\Controllers\Management;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\AdditionalCost;
use App\Models\Payment;
use App\Models\Property;
use App\Queries\AppQuery;
use App\Services\MpesaService;
use App\Services\SMSService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends ApiController
{
    protected MpesaService $mpesa;
    protected SMSService $sms;

    public function __construct(MpesaService $mpesa, SMSService $sms)
    {
        $this->mpesa = $mpesa;
        $this->sms = $sms;
    }

    public function getAccessToken()
    {
        $token = $this->mpesa->getAccessToken();
        return response(['token', $token]);
    }

    public function stkPush()
    {
        $body = $this->mpesa->initiateSTK('254705180969', 1);
        return response($body);
    }

    public function smsid(){
        $body = $this->mpesa->getAccessToken();
        return response($body);
    }

    public function payments(Request $request)
    {
        try{
            $user = $request->user();
            $page = $request->input('page') ?? 1;
            $searchTerm = $request->input('query') ?? null;
            $year = intval($request->input('year')) ?? Carbon::now()->year;
            $month = $request->input('month') ?? null;
            $perPage = 15;
            $isTenant = $user->role == 'tenant';
            $isAgent =  $user->role == 'agent';
            if($isTenant){
                $payments = AppQuery::paymentqueries()->where(['user_id'=>$user->id, 'year'=>$year])
                                                        ->where(function ($query) use ($month){
                                                            if(!is_null($month)){
                                                                $query->where('month', $month);
                                                            }
                                                        })->when($searchTerm, function ($query) use ($searchTerm) {
                                                            if(!is_null($searchTerm)){
                                                                $query->whereHas('user', function ($q) use ($searchTerm) {
                                                                    $q->where('number', $searchTerm);
                                                                });
                                                            }
                                                        })->paginate($perPage, ['*'], 'page', $page);              
                return PaymentResource::collection($payments)->response();
            }
            $properties = $isAgent ? $user->agentProperties : $user->landlordProperties;
            $ids = $properties->pluck('id');
            $payments = AppQuery::paymentqueries()->where(['year'=>$year])
                                ->whereIn('property_id', $ids)
                                ->where(function ($query) use ($month){
                                    if(!is_null($month)){
                                        $query->where('month', $month);
                                    }
                                })->when($searchTerm, function ($query) use ($searchTerm) {
                                    if(!is_null($searchTerm)){
                                        $query->whereHas('user', function ($q) use ($searchTerm) {
                                            $q->where('number', $searchTerm);
                                        });
                                    }
                                })->paginate($perPage, ['*'], 'page', $page);
            return PaymentResource::collection($payments)->response();


        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    public function payment(Request $request)
    {
        try{
            $request->validate([
                'property_id'=>['required', 'string', 'exists:properties,id'],
                'user_id'=> ['required', 'string', 'exists:users,id'],
                'tenancy_id'=>['required', 'string', 'exists:tenancies,id'],
                'payment_method'=>['nullable', 'string'],
                'date'=>['nullable', 'string'],
                'costs'=>['required', 'array']
            ]);
            $costs = $request->costs;
            $year = Carbon::now()->year;
            $payments = array_map(
            fn($cost, $index) => [
                'id'=>Str::ulid(),
                'property_id'=>$request->property_id,
                'user_id'=>$request->user_id,
                'tenancy_id'=>$request->tenancy_id,
                'cost_id'=> $cost['title'] == 'rent' ? null : $cost['id'],
                'payment_method'=>$request->payment_method,
                'reference_code'=>$cost['reference_code'] ?? null,
                'date'=>$request->date,
                'description'=>$cost['description'] ?? null,
                'payment_type'=>$cost['title'],
                'amount_due'=>$cost['cost'],
                'amount_paid'=>$cost['amount_paid'],
                'balance'=>max(0,(float) $cost['cost'] - (float) $cost['amount_paid']),
                'created_at'=>now(),
                'updated_at'=>now(),
                'year'=>$year
            ],$costs, array_keys($costs)
            );
            Payment::insert($payments);

            return $this->success($payments, 'Payment added successfully');


        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    public function editPayment(Request $request, string $id)
    {
        try{
            $request->validate([
                'user_id'=> ['required', 'string'],
                'tenancy_id'=>['required', 'string'],
                'amount'=>['required', 'integer'],
                'payment_method'=>['nullable', 'string'],
                'reference_code'=>['nullable', 'string'],
                'paid_for'=>['nullable', 'string']
            ]);
            $payment  = Payment::find($id);
            if(!$payment){
                return $this->notFound('Payment not found');
            }
            $payment->update([
                'user_id'=>$request->user_id,
                'tenancy_id'=>$request->tenancy_id,
                'amount'=>$request->amount,
                'payment_method'=>$request->payment_method,
                'reference_code'=>$request->reference_code,
                'paid_for'=>$request->paid_for
            ]);
            return $this->success($payment->refresh(), 'payment updated');


        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    public function addCost(Request $request, string $id)
    {
        try{
            $request->validate([
                'title'=>['required', 'string'],
                'cost'=>['required', 'integer']
            ]);
            $property = Property::exists($id);
            if(!$property){
                return $this->notFound('Property not found');
            }
            $add = AdditionalCost::create([
                'title'=>$request->title,
                'cost'=>$request->cost,
                'property_id'=>$id
            ]);
            return $this->success($add, 'Added cost successfully');


        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    public function editCost(Request $request, string $id)
    {
        try{
            $request->validate([
                'title'=>['required', 'string'],
                'cost'=>['required', 'integer'],
            ]);
            $cost = AdditionalCost::find($id);
            if(!$cost){
                return $this->notFound('Cost not found');
            }
            $cost->update([
                'title'=>$request->title,
                'cost'=>$request->cost
            ]);
            return $this->success($cost->fresh(), 'Cost updated');

        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

    public function propertyCosts(string $id)
    {
        try{
            $costs = AdditionalCost::where('property_id', $id)->get();
            return response($costs, 200);

        }catch(Exception $e){
            $error = $e->getMessage();
            return $this->error($error);
        }
    }

   
}
