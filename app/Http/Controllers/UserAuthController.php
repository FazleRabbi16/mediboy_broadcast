<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\Cart;
use App\Models\DeliveryAddress;
use App\Models\ActiveDeliveryAddress;
use App\Models\Otp;
use Validator;
use Auth;

class UserAuthController extends Controller
{
public function login(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'phoneNumber' => 'required',
        'password' => 'required',
    ]);

    // Validate error message response
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()]);
    } else {
        $phoneNumber = $request->input('phoneNumber');
        $user = User::where('phoneNumber', $phoneNumber)->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response([
                'error' => 'Incorrect number or password'
            ], 401);
        }

        // Create token
        $token = $user->createToken('myapp', ['user'])->plainTextToken;

        // Get carts value if exist
        $carts = Cart::with('product:id,productName,genericName,cart_text,retail_max_price,unit_in_pack,type,quantity,prescription,feature,coverImage')
            ->where('user_id', $user->id)
            ->get();

        // Get balance and attach to user
        $balance = UserAccount::where('user_id', $user->id)->first();
        $user->balance = $balance ? $balance->balance : 0.00;

        // Get active delivery address details
        $address = null;
        $active_delivery_address = ActiveDeliveryAddress::where('user_id', $user->id)->first();

        if ($active_delivery_address) {
            $deliver_address_id = $active_delivery_address->delivery_address_id;
            $address = DeliveryAddress::find($deliver_address_id);
        }

        // Prepare response
        $response = [
            'user' => $user,
            'carts' => $carts,
            'address' => $address,
            'token' => $token,
        ];

        return response($response, 201);
    }
}


public function register(Request $request)
{

//Validate the request
$validator = Validator::make($request->all(), [
'firstName'=>'required',
'lastName'=>'required',
'phoneNumber'=>'required|unique:users',
'password'=>'required',
'bloodGroup'=>'required',
'donateBlood'=>'required',
'termsConditions'=>'required',
'status'=>'required',
]);
// validate error message response
if ($validator->fails()) {
return response()->json(['errors'=>$validator->errors()]);
}else{
//default status for register user

$user= User::create([
    'firstName'=>$request->input('firstName'),
    'lastName'=>$request->input('lastName'),
    'phoneNumber'=>$request->input('phoneNumber'),
    'password'=>bcrypt($request->input('password')),
    'division'=>$request->input('division'),
    'district'=>$request->input('district'),
    'upazilla'=>$request->input('upazilla'),
    'bloodGroup'=>$request->input('bloodGroup'),
    'donateBlood'=>$request->input('donateBlood'),
    'termsConditions'=>$request->input('termsConditions'),
    'status'=>$request->input('status'),
]);
$balance= UserAccount::create(['user_id'=>$user->id,'balance'=>0.00]);
$response = [
    'message'=>"User created successfully",
    // 'token'=>$token
];
return response($response,201);
}
}

public function userDetails(){
$user = Auth::user();
return response()->json(['data'=>$user]);
}
//update user profile
public function updateProfile(Request $request)
{
//Validate the request
$validator = Validator::make($request->all(), [
'firstName'=>'required',
'lastName'=>'required',
'division'=>'required',
'district'=>'required',
'upazilla'=>'required',
'bloodGroup'=>'required',
'donateBlood'=>'required',
]);
// validate error message response
if ($validator->fails()) {
return response()->json(['errors'=>$validator->errors()]);
}else{
$user=Auth::user();
if (!$user) {
return response([
    'msg' => 'User not exist'
], 401);
}
$user->update([
'firstName'=>$request->input('firstName'),
'lastName'=>$request->input('lastName'),
'division'=>$request->input('division'),
'district'=>$request->input('district'),
'upazilla'=>$request->input('upazilla'),
'bloodGroup'=>$request->input('bloodGroup'),
'donateBlood'=>$request->input('donateBlood'),
]);
return response()->json(['msg'=>'Profile Updated Successfully']);
}
}

//update user password
public function updatePassword(Request $request)
{
//Validate the request
$validator = Validator::make($request->all(), [
'current_passowrd'=> 'required',
'new_passowrd'=> 'required',
]);
// validate error message response
if ($validator->fails()) {
return response()->json(['errors'=>$validator->errors()]);
}
$user=Auth::user();
$current_passowrd=$request->input('current_passowrd');
$new_passowrd=$request->input('new_passowrd');
if (!Hash::check($request->input('current_passowrd'), $user->password)) {
return response([
    'err' => 'incorrect password'
]);
}
$user->update(['password'=>bcrypt($new_passowrd)]);
return response()->json(['msg'=>'Password Change Successfully']);
}

public function logout(Request $request)
{
    $user = Auth::user();
    // Retrieve the token from the request or from the authenticated user's token
    $token = $user->currentAccessToken();
    // Revoke the specific token
    $token->delete();
    // You can perform additional logout actions if needed
    return response()->json(['message' => 'Successfully logged out']);
}
    
// password reset
public function resetPassword(Request $request)
{
   //Validate the request
   $validator = Validator::make($request->all(), [
    'password'=> 'required',
    'phoneNumber'=> 'required',
    'otp' => 'required|digits:4',
    ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }
    $password=$request->input('password');
    $phoneNumber=$request->input('phoneNumber');
    $otp=$request->input('otp');
    // Retrieve the OTP record from the database
    $otpRecord = Otp::where('phoneNumber', $phoneNumber)
    ->where('otp',$otp)
    ->first();
    // Check if the OTP record exists and if it is still valid
    if ($otpRecord && $otpRecord->expire_at > now()) {
    // $otpRecord->delete();
    $user = User::where('phoneNumber',$phoneNumber)->first();
    if (!$user) {
    return response(['error' => 'No user exist']);
    }else{
      $user->update(['password'=>bcrypt($password)]);
      // Delete all OTP records for the matching phone number after password reset
        Otp::where('phoneNumber', $phoneNumber)->delete();
      return response(['msg' => 'Successfully password change']);  
    }
    } else {
    // Delete all OTP records for the matching phone number after password reset
    Otp::where('phoneNumber', $phoneNumber)->delete();
    // OTP is invalid or expired
    return response()->json(['error' => 'Time over for this given otp,please try again by refresh the page']);
    }
    
}
}
