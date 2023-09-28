<?php 
namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use App\Models\StoreAdmin;
use App\Models\DeliveryBoy;
use App\Models\Admin;
use App\Models\Cart;
use Illuminate\Http\Response;

class AuthController extends Controller
{

    
    public function login(LoginUserRequest $request){
    
        $credentials = $request->only('phone', 'password');
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();
        return response()->json([
            'status' => 'success',
            'user' => User::where('id', $user->id)->first(),
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(RegisterUserRequest $request){
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role,
        ]);

        // Create the related user type based on the user's role
        $this->createRelatedModel($request->role,$user,$request);
        
        // Authenticate the user and generate a token
        $token = Auth::login($user);

        $user->assignRole($request->role);
        if($user->hasAnyRole(['customer'])){
            $cart = new Cart();
            $cart->user()->associate($user);
            $cart->save();
        }
        
        return response()->json([
                'status' => 'success',
                'message' => 'User created successfully and authenticated',
                'user' => User::with($request->role)->where('id', $user->id)->first(),
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ],
            ],Response::HTTP_CREATED);
    }
    protected function createRelatedModel(string $role, User $user, Request $request){
        switch ($role) {
            case 'customer':
                return Customer::create([
                    'user_id' => $user->id,
                ]);
            case 'storeAdmin':
                return StoreAdmin::create([
                    'user_id' => $user->id,
                    'facebook' => $request->facebook,
                    'twitter' => $request->twitter,
                    'instagram' => $request->instagram,
                ]);
            case 'deliveryBoy':
                return DeliveryBoy::create([
                    'user_id' => $user->id,
                    'start_point_address' => $request->start_point_address,
                    'region' => $request->region,
                ]);
            case 'admin':
                return Admin::create([
                    'user_id' => $user->id,
                    'facebook' => $request->facebook,
                    'twitter' => $request->twitter,
                    'instagram' => $request->instagram,
                ]);
            default:
                return null;
        }
    }

    public function logout(){
        if(Auth::check()){
            Auth::logout();
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function refresh(){
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
}