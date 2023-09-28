<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\DeliveryCostController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('auth')->group(function (){
    Route::post('register',[AuthController::class,'register']);
    Route::post('login',[AuthController::class,'login']);
    Route::post('logout',[AuthController::class,'logout'])->middleware('auth:api');
    Route::post('refresh',[AuthController::class,'refresh'])->middleware('auth:api');
});

Route::prefix('categories')->group(function (){
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store'])->middleware('auth:api');
    Route::put('/{id}', [CategoryController::class, 'update'])->middleware('auth:api');
    Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware('auth:api');
});

Route::prefix('stores')->middleware('auth:api')->group( function (){
    Route::get('/', [StoreController::class, 'index']);
    Route::post('/', [StoreController::class, 'store']);
    Route::put('update/{store_id}',[StoreController::class,'update']);
    Route::delete('delete/{store_id}',[StoreController::class,'delete']); 
    Route::get('/{category}',[StoreController::class,'filterStoresByCategory']); 
});

Route::prefix('products/{store_id}')->middleware('auth:api')->group(function (){
    Route::get('/',[ProductsController::class,'index']);
    Route::post('/',[ProductsController::class,'store']);
    Route::put('update/{product_id}',[ProductsController::class,'update']);
    Route::delete('delete/{product_id}',[ProductsController::class,'delete']);                       
});
Route::get('allProducts',[ProductsController::class,'allProducts']);
Route::prefix('cart')->middleware('auth:api')->group(function () {
    Route::get('/',[CartController::class, 'getCartItems']);
    Route::post('/', [CartController::class,'addItemToCart']);
    Route::put('/{cart_item_id}', [CartController::class,'updateCartItemQuantity']);
    Route::delete('/{cart_item_id}', [CartController::class,'removeCartItem']);
    Route::get('/total-price', [CartController::class,'getTotalPrice']);
});

Route::prefix('orders')->middleware('auth:api')->group(function () {
    Route::post('/make_order', [OrderController::class, 'makeOrder']);
    Route::put('/take_order/{order_id}', [OrderController::class, 'takeOrder']);
    Route::get('/ordered', [OrderController::class, 'getOrderedOrders']);
    Route::put('/{order_id}/delivered', [OrderController::class, 'setOrderDelivered']);
});

Route::prefix('deliveryCost')->group(function () {
    Route::get('/', [DeliveryCostController::class, 'index']);
    Route::post('/', [DeliveryCostController::class, 'store']);
    Route::put('/{id}', [DeliveryCostController::class, 'update']);
    Route::delete('/{id}', [DeliveryCostController::class, 'destroy']);
});