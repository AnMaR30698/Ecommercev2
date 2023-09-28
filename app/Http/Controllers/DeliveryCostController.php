<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DeliveryCostController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if($user->hasAnyRole(['admin'])){
            $deliveryCosts = DeliveryCost::all();
    
            return response()->json([
                'status' => 'success',
                'data' => $deliveryCosts,
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'you are not admin you can not access here.',
                
            ]);
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if($user->hasAnyRole(['admin'])){
        $validatedData = $request->validate([
            'distance' => 'required|integer|unique:delivery_costs',
            'cost' => 'required|numeric',
        ]);

        $deliveryCost = DeliveryCost::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery cost created successfully.',
            'data' => $deliveryCost,
        ], 201);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'you are not admin you can not access here.',
                
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if($user->hasAnyRole(['admin'])){
        $deliveryCost = DeliveryCost::find($id);
            if(!$deliveryCost){
                return response()->json([
                    'status' => 'error',
                    'message' => 'it is  not found.',
                ],Response::HTTP_NOT_FOUND);
            }
        $validatedData = $request->validate([
            'distance' => ['required', 'integer', Rule::unique('delivery_costs')
                                                      ->ignore($deliveryCost->id)],
            'cost' => 'required|numeric',
        ]);

        $deliveryCost->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery cost updated successfully.',
            'data' => $deliveryCost,
        ]);}else{
            return response()->json([
                'status' => 'error',
                'message' => 'you are not admin you can not access here.',
                
            ]);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if($user->hasAnyRole(['admin'])){
        $deliveryCost = DeliveryCost::findOrFail($id);
        $deliveryCost->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery cost deleted successfully.',
        ]);
    }else{
        return response()->json([
            'status' => 'error',
            'message' => 'you are not admin you can not access here.',
            
        ]);
    }
    }
}
