<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStoreRequest;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    Public function index(){
        $user = Auth::user();
        $stores = $user->stores;
        $storesWithImages = [];

        foreach ($stores as $store) {
            $url = Storage::url($store->image);
            $storesWithImages[] = [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'store_phone' => $store->store_phone,
                'store_address' => $store->store_address,
                'latitude' => $store->latitude,
                'longitude' => $store->longitude,
                'image' => $url,
                'twitter' => $store->twitter,
                'facebook' => $store->facebook,
                'instagram' => $store->instagram,
                'categories'=>$store->categories,
            ];
        }
        return response()->json([
            'status' => 'success',
            'message' => 'your stores.',
            'store' => $storesWithImages,
        ],Response::HTTP_ACCEPTED);
    }
    public function store(StoreStoreRequest $request)
    {
        $user = Auth::user();

        if($user->hasAnyRole(['admin', 'storeAdmin'])){
            $store = new Store();
            $store->store_name=$request->store_name;
            $store->store_phone=$request->store_phone;
            $store->store_address=$request->store_address;
            $store->latitude=$request->latitude;
            $store->longitude=$request->longitude;
            $path = $request->file('image')->store('public/images');
            $url = Storage::url($path);
            $store->image = $path;
            $store->twitter=$request->twitter;
            $store->facebook=$request->facebook;
            $store->instagram=$request->facebook;
            $store->user_id = $user->id; 
            $categories = $request->categories;
            $store->save();
            $store->categories()->attach($categories);
            $categories = $store->categories()->pluck('name');
        return response()->json([
            'status' => 'success',
            'message' => 'Store created successfully.',
            'store_name' => $request->store_name,
            'store_phone' => $request->store_phone,
            'store_address' => $request->store_address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'image' => $url,
            'twitter' => $request->twitter,
            'facebook' => $request->facebook,
            'instagram' => $request->instagram,
            'categories'=>$categories,
        ],Response::HTTP_ACCEPTED);
        }
        else{
            return response()->json([
                'status' => 'error',
                'message' => 'you can not access here.',
            ],Response::HTTP_FORBIDDEN);
        }

        
    }
    public function update(StoreStoreRequest $request, $store_id ){
        $user = Auth::user();
        if($user->hasAnyRole(['admin', 'storeAdmin']) ){
            $store = Store::find($store_id);
            if(!$store){
                return response()->json([
                    'status' => 'error',
                    'message' => 'store not found',
                ],Response::HTTP_NOT_FOUND);
            }
            if($user->hasRole('storeAdmin')){
                if($user->id!==$store->user_id){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'you do not have this store',
                    ],Response::HTTP_FORBIDDEN);
                }
            }

            $store->store_name=$request->store_name;
            $store->store_phone=$request->store_phone;
            $store->store_address=$request->store_address;
            $path = $request->file('image')->store('public/images');
            $url = Storage::url($path);
            $store->image = $path;
            $store->twitter=$request->twitter;
            $store->facebook=$request->facebook;
            $store->instagram=$request->facebook;
            $store->user_id = $user->id; 
            // $categories = $request->categories;
            $store->save();
            // $categories = $request->categories;
            $existingCategories = $store->categories()->pluck('category_id')->toArray();

            // Get the list of new categories
            $newCategories = $request->categories;

            // Detach any categories that are not in the new list
            $categoriesToRemove = array_diff($existingCategories, $newCategories);
            $store->categories()->detach($categoriesToRemove);

            // Attach any new categories that are not already attached
            $categoriesToAdd = array_diff($newCategories, $existingCategories);
            $store->categories()->attach($categoriesToAdd);

            // Retrieve the names of the categories that are attached to the store
            $categories = $store->categories()->pluck('name');

            // $store->categories()->syncWithoutDetaching($categories);
            // $categories = $store->categories()->pluck('name');
            return response()->json([
                'status' => 'success',
                'message' => 'Store updated successfully.',
                'store' => $store,
                'categories'=>$categories,
            ],Response::HTTP_ACCEPTED);
            }else{
            return response()->json([
                'status' => 'error',
                'message' => 'you can not access here.',
            ],Response::HTTP_FORBIDDEN);
        }
    }
    public function delete($store_id)
    {
        $user = Auth::user();
        if($user->hasAnyRole(['admin', 'storeAdmin']) ){
            $store = Store::find($store_id);
            if(!$store){
                return response()->json([
                    'status' => 'success',
                    'message' => 'this store is not exist',
                ], Response::HTTP_FORBIDDEN);
            }
            if($user->hasRole('storeAdmin')){
                if($user->id!==$store->user_id){
                    return response()->json([
                        'status' => 'error',
                        'message' => 'you do not have this store',
                    ],Response::HTTP_FORBIDDEN);
                }
            }
            $store->delete();
            return response()->json([
                'status'=>'success',
                'message' => 'store deleted successfully'
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'you can not access here.',
            ],Response::HTTP_FORBIDDEN);
        }
       

        
    }

    public function filterStoresByCategory($category)
    {
        $query = Store::query();

    if ($category) {
        $category = Category::where('name', $category)->firstOrFail();
        $query->whereHas('categories', function ($query) use ($category) {
            $query->where('category_id', $category->id);
        });
    }

        $stores = $query->get();

        if(!$stores){
            return response()->json([
                'status' => 'success',
                'message' => 'no stores with this category.',
                'stores' =>$stores
            ]);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'this is the stores with the category.',
            'stores' =>$stores
        ]);
    }

    
}
