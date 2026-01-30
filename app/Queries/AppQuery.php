<?php
namespace App\Queries;
use App\Models\Property;

class AppQuery
{
    public static function propertyQueries()
    {
        return Property::query()->with('landlord', 'agent');
    }

}
