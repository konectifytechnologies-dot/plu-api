<?php
namespace App\Queries;

use App\Models\Payment;
use App\Models\Property;

class AppQuery
{
    public static function propertyQueries()
    {
        return Property::query()->with([
                                'landlord:id,email,number,name',
                                'agent:id,email,number,name',
                                'units:id,property_id,name', // load units
                                'units.tenancy' => function ($q) {
                                    $q->where('status', 'active'); // only active tenancies
                                },
                            ]);
    }

    public static function paymentqueries()
    {
        return Payment::query()->with([
            'user:id,name,number',
            'tenancy',
            'property'
        ]);
    }

} 
