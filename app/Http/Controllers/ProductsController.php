<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    public function index($store_id){
        $store = Store::where('id', $store_id)->first();
            if(!$store){
                return response()->json([
                    'status' => 'success',
                    'message' => 'this store is not exist',
                ], Response::HTTP_FORBIDDEN);
            }
        $products = $store->products;
        $productsWithImages = [];

        foreach ($products as $product) {
            $url = Storage::url($product->image);
            $productsWithImages[] = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $url,
                'store_id'=>$product->store_id
            ];
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'products' => $productsWithImages,
        ], Response::HTTP_OK);
    }
    public function allProducts(){
        $products = Product::all();
        $productsWithImages = [];
        foreach ($products as $product) {
            $url = Storage::url($product->image);
            $productsWithImages[] = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'image' => $url,
                'store_id'=>$product->store_id
            ];
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'products' => $productsWithImages,
        ], Response::HTTP_OK);
    }
    public function store(StoreProductRequest $request, $store_id){
        $user = Auth::user();
        if($user->hasAnyRole(['admin', 'storeAdmin'])){
            $store = Store::where('id', $store_id)->first();
            if(!$store){
                return response()->json([
                    'status' => 'success',
                    'message' => 'this store is not exist',
                ], Response::HTTP_FORBIDDEN);
            }
            $ifHasThisProduct = Product::where('store_id', $store_id)
                                    ->where('name', $request->name)
                                    ->where('description',$request->description)
                                    ->first();

            if($ifHasThisProduct){
                return response()->json([
                        'status' => 'error',
                        'message' => 'Product with this name and description already exists',
                        'product' => $ifHasThisProduct,
                    ], Response::HTTP_CONFLICT);
            }
            $product = new Product();
            $product->name = $request->name;
            $product->price = $request->price;
            $product->description = $request->description;
            $product->store_id = $store_id;
            $path = $request->file('image')->store('public/images');
            $url = Storage::url($path);
            $product->image = $path;
            $product->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully.',
                'product-name' => $product->name,
                'product-description' => $product->description,
                'product-price' => $product->price,
                'store_id' => $product->store_id,
                'product-image'=>$url,
            ],Response::HTTP_ACCEPTED);
        }
        else{
            return response()->json([
                'status' => 'error',
                'message' => 'you can not access here.',
            ],Response::HTTP_FORBIDDEN);
        }
    }
    public function update(StoreProductRequest $request, $store_id ,$product_id){
        $user = Auth::user();
        if($user->hasAnyRole(['admin', 'storeAdmin'])){
            $product = Product::find($product_id);
            if(!$product){
                return response()->json([
                    'status' => 'error',
                    'message' => 'product not found',
                ],Response::HTTP_NOT_FOUND);
            }
            $sameProduct = Product::where('name', $request->name)
                                  ->where('description',$request->description)
                                  ->whereNotIn('id', [$product_id])->first();
            if($sameProduct){
            return response()->json([
                'status' => 'error',
                'message' => 'Product with this name and description already exists',
            ],Response::HTTP_NOT_FOUND);
            }
            $product->name = $request->name;
            $product->price = $request->price;
            $product->description = $request->description;
            $product->store_id = $store_id;
            $path = $request->file('image')->store('public/images');
            $url = Storage::url($path);
            $product->image = $path;
            $product->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully.',
                'product-name' => $product->name,
                'product-description' => $product->description,
                'product-price' => $product->price,
                'store_id' => $product->store_id,
                'product-image'=>$url,
            ],Response::HTTP_ACCEPTED);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'you can not access here.',
            ],Response::HTTP_FORBIDDEN);
        }
    }
    public function delete($store_id,$product_id)
    {
        $store = Store::where('id', $store_id)->first();
            if(!$store){
                return response()->json([
                    'status' => 'success',
                    'message' => 'this store is not exist',
                ], Response::HTTP_FORBIDDEN);
            }
        $product = Product::find($product_id);

        if (!$product) {
            return response()->json([
                'status'=>'erorr',
                'message' => 'Product not found'
            ], Response::HTTP_NOT_FOUND);
        }
        $product->delete();
        return response()->json([
            'status'=>'success',
            'message' => 'Product deleted successfully'
        ]);
    }
}
