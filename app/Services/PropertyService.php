<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Tenancy;
use App\Models\PropertyUser;
use Illuminate\Database\Eloquent\Collection;

class PropertyService
{
    public function attachUserToProperty(string $propertyId, string $userId,string $role) 
    {
        try{
            // Prevent duplicate role assignment
            $errors = [];
            $exists = PropertyUser::where('property_id', $propertyId)
                ->where('user_id', $userId)
                ->where('role', $role)
                ->exists();

            if ($exists) {
                $userExists = [
                    'error'=>'A'.$role.'Already exists for this property',
                    'code'=>3
                ];
                $errors[] = $userExists;
            }

            if(!empty($errors)){
                return $errors;
            }

            PropertyUser::create([
                'property_id' => $propertyId,
                'user_id'     => $userId,
                'role'        => $role,
            ]);
        }catch(Exception $e){
            $response = ['error'=>$e->getMessage(), 'code'=>3];
            return $response;
        }

    }

    public function createTenancy(User $user, string $property_id, string $unit_id)
    {
        try{
            $tenancy = Tenancy::create([
                'user_id'=>$user->id,
                'property_id'=>$property_id,
                'unit_id'=>$unit_id,
                'start_date'=>now(),
                'status'=>'active'
            ]);

            return $tenancy;
            

        }catch(Exception $e){
            $response = ['error'=>$e->getMessage(), 'code'=>3];
            return $response;
        }
    }

    public function updateTenancy(string $id, string $property_id, string $unit_id)
    {
        try{

            $tenancy = Tenancy::find($id);
            if(!$tenancy){
                return 'Tenancy Not found';
            }
            $tenancy->update([
                'property_id'=>$property_id,
                'unit_id'=>$unit_id,
                'start_date'=>now(),
            ]);

            return $tenancy->fresh();

        }catch(Exception $e){
            $response = ['error'=>$e->getMessage(), 'code'=>3];
            return $response;
        }
    }


    
}