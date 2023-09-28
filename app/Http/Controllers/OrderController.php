<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\DeliveryBoy;
use App\Models\DeliveryCost;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{

    public function makeOrder()
    {
        $user = Auth::user();
        if($user->hasAnyRole(['customer'])){
        $cart = Cart::where('user_id', $user->id)->first();
    
        $order = $cart->makeOrder();
        if (!$order) {
            return response()->json([
                'status'=>'error',
                'message'=>'there is nothing to order'
            ]);
        }
    
        // Get the order items and related product and store information
        $orderItems = $this->getOrderItemsInfo($order->orderItems);

        return response()->json([
            'order_id' => $order->id,
            'total_price' => $order->total_price,
            'state' => $order->state,
            'order_details' => $orderItems,
        ]);
        }else{
            return response()->json([
                'status'=>'error',
                'message'=>'you are not customer you can not order'
            ]);
        }
    }
    public function completeOrderInfo($order_id, $customerLatitude, $customerLongitude)
    {
        // Retrieve the order and order items
        $order = Order::with('orderItems.product.store')->where('id', $order_id)->first();
        $orderItems = $order->orderItems;
        // Group order items by store
        $orderItemsByStore = [];
        $maxDistance = 0;
        foreach ($orderItems as $orderItem) {
            $store = $orderItem->product->store;
            $storeLatitude = $store->latitude;
            $storeLongitude = $store->longitude;
            $storeId = $store->id;
            // Calculate distance and store it in the grouped array
            if (!isset($orderItemsByStore[$storeId])) {
                $orderItemsByStore[$storeId] = [
                    'store_name' => $store->store_name,
                    'distance' => null,
                    'products' => [],
                ];
                // Calculate distance for the store
                $orderItemsByStore[$storeId]['distance'] = $this->calculateDistance($customerLatitude, $customerLongitude, $storeLatitude, $storeLongitude);
            }
            // Update farthest store if the distance is greater
            if ($orderItemsByStore[$storeId]['distance'] > $maxDistance) {
                $farthestStore = $store;
                $maxDistance = $orderItemsByStore[$storeId]['distance'];
            }
            // Add the product to the store's products array
            $orderItemsByStore[$storeId]['products'][] = [
                'product_name' => $orderItem->product->name,
                'product_description' => $orderItem->product->description,
                'product_price' => $orderItem->product->price,
                'product_image' => $orderItem->product->image,
            ];
        }
        $deliveryCost = $this->calculateDeliveryCost($maxDistance);
        // Return the data as a JSON response.
        return response()->json([
            'status' => 'success',
            'message' => 'order information:',
            'order_id'=> $order->id,
            'data' => array_values($orderItemsByStore),
            'delivery-cost' => $deliveryCost,
        ]);
    }
    public function getOrderedOrders()
    {
        $orders = Order::where('state', 'ordered')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'orders in wait',
            'orders' => $orders,
        ]);
    }
    private function calculateDeliveryCost($distance)
    {
        // Retrieve the delivery cost from the delivery_costs table based on the distance
        $deliveryCost = DeliveryCost::where('distance', '>=', $distance)
            ->orderBy('distance', 'asc')
            ->pluck('cost')
            ->first();

        return $deliveryCost;
    }
    
    public function getOrderItemsInfo($Items){
        $orderItems = [];
        foreach ($Items as $orderItem) {
            $product = Product::with('store')->find($orderItem->product_id);
            $store = $product->store;
            $orderItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_price' => $product->price,
                'store_id' => $store->id,
                'store_name' => $store->store_name,
                'store_address' => $store->store_address,
                'quantity' => $orderItem->quantity,
                'price' => $orderItem->price,
            ];
        }
        return $orderItems;
    }
    
    public function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $earthRadius = 6371; // radius of the Earth in kilometers
        $latDifference = deg2rad($latitude2 - $latitude1);
        $lonDifference = deg2rad($longitude2 - $longitude1);
        $a = sin($latDifference / 2) * sin($latDifference / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($lonDifference / 2) * sin($lonDifference / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        return $distance;
    }
    
    
    public function takeOrder( $order_id)
    {
        $user = Auth::user();
        if($user->hasAnyRole(['deliveryBoy'])){
            $deliveryBoy = $user->deliveryBoy->id;
            $order = Order::where('id', $order_id)->first();
            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found',
                    
                ]);
            }
            if($order->state=='on_the_way' && $order->delivery_boy_id ==$deliveryBoy){
                $orderItems = $this->getOrderItemsInfo($order->orderItems);
                return response()->json([
                    'status' => 'error',
                    'message' => 'you had already taken this order',
                    'order_id' => $order->id,
                    'total_price' => $order->total_price,
                    'state' => $order->state,
                    'order_details' => $orderItems,
                ]);
            }elseif($order->state=='on_the_way'){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order already taken ',
                    
                ]);
            }
            $order->delivery_boy_id = $deliveryBoy;
            $order->state = 'on_the_way';
            $order->save();
            $orderItems = $this->getOrderItemsInfo($order->orderItems);

            return response()->json([
                'status' => 'success',
                'message'=> 'this order for you deliver it faster as you can',
                'order_id' => $order->id,
                'total_price' => $order->total_price,
                'state' => $order->state,
                'order_details' => $orderItems,
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'you are not delivery boy you can not access here.',
                
            ]);
        }

        // Return the updated order
    }

    public function setOrderDelivered($order_id)
    {
        $user= Auth::user();
        if($user->hasAnyRole(['customer'])){
        $order = Order::where('id', $order_id)->where('customer_id',$user->id)->first();
        $order->state = 'delivered';
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order marked as delivered',
        ]);
    }else{
        return response()->json([
            'status' => 'error',
            'message' => 'you are not delivery boy you can not access here.',
            
        ]);
    }
    }
}
