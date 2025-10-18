<?php
namespace App\Http\Controllers\Pharmacy;

use App\Models\PharmacyUser;
use App\Models\Otp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Validator;
use Auth;

class PharmacyAuthController extends Controller
{


  public function register(Request $request)
  {
      
        // validation before update
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNumber' => 'required|unique:pharmacy_users,phoneNumber',
            'email' => 'required',
            'password' => 'required',
            'role' => 'required',            
      ]);
      // validate error message response
      if ($validator->fails()) {
          return response()->json(['errors'=>$validator->errors()]);
      }else{
        $pharmacyUser = Auth::guard('pharmacy')->user();
        $pharmacy_id=$pharmacyUser->pharmacy_id;
        $firstName = $request->input('firstName');
        $lastName = $request->input('lastName');
        $phoneNumber = $request->input('phoneNumber');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');
        
        $addPharmacyUser=PharmacyUser::create([
            'firstName'=>$firstName,
            'lastName'=>$lastName,
            'phoneNumber'=>$phoneNumber,
            'email'=>$email,
            'password'=>bcrypt($password),
            'role'=>$role,
            'pharmacy_id'=>$pharmacy_id
        ]);
        }
        return response()->json([
            'success'=>'Data Submitted Successfully',
         ]);
  }

public function login(Request $request)
 {
      //Validate the request
      $validator = Validator::make($request->all(), [
         'phoneNumber'=> 'required',
         'password'=> 'required',
     ]);
     // validate error message response
     if ($validator->fails()) {
         return response()->json(['errors'=>$validator->errors()]);
     }else{
         $phoneNumber = $request->input('phoneNumber');
         $user = PharmacyUser::where('phoneNumber',$phoneNumber)->first();

         if (!$user || !Hash::check($request->input('password'), $user->password)) {
                     return response([
                         'msg' => 'incorrect email or password'
                     ], 401);
         }
         $token = $user->createToken('mediboy',['pharmacy'])->plainTextToken;
         $response = [
             'user'=>$user,
             'token'=>$token
         ];
     return response($response,201);

     }
 }
 public function details()
 {
    $user = Auth::guard('pharmacy')->user();
    return response()->json(['data'=>$user]);
 }
 public function logout(Request $request)
    {

        $user = Auth::guard('pharmacy')->user();
        // Retrieve the token from the request or from the authenticated user's token
        $token = $user->currentAccessToken();
        // Revoke the specific token
        $token->delete();
        // You can perform additional logout actions if needed
        return response()->json(['message' => 'Successfully logged out']);
    }
    public function remove($id)
    {
    $user = PharmacyUser::find($id);
    $user_role = $user->role;
    if($user_role == 'admin')
    {
        return response()->json(['error'=>'An admin would not remove']);
        exit();
    }else{
        $user->delete();
        return response()->json(['success'=>'User Remove Successfully']);
     }
    }

    public function updateProfile(Request $request)
    {
    //Validate the request
     $validator = Validator::make($request->all(), [
        'firstName'=>'required',
        'lastName'=>'required',
        'role'=>'required',
    ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
      $user=Auth::guard('pharmacy')->user();
      if (!$user) {
        return response([
            'msg' => 'User not exist'
        ], 401);
      }
      $user->update([
        'firstName'=>$request->input('firstName'),
        'lastName'=>$request->input('lastName'),
        'role'=>$request->input('role'),
       ]);
      return response()->json(['msg'=>'Profile Updated Successfully']);
     }
    }
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
        $user=Auth::guard('pharmacy')->user();
        $current_passowrd=$request->input('current_passowrd');
        $new_passowrd=$request->input('new_passowrd');
        if (!Hash::check($request->input('current_passowrd'), $user->password)) {
        return response([
            'msg' => 'incorrect password'
        ], 401);
        }
        $user->update(['password'=>bcrypt($new_passowrd)]);
        return response()->json(['msg'=>'Password Change Successfully']);
    }

    // pharmacy admin/sub-admin only see the own pharmacy user list
public function get_user_list()
    {
    $user = Auth::guard('pharmacy')->user();
    $user_id = $user->id;
    $user_pharmacy_id = $user->pharmacy_id;
    $user_list = PharmacyUser::where('id', '!=', $user_id)
                 ->where('pharmacy_id',$user_pharmacy_id)->get();
    return response()->json(['user'=>$user_list]);
    }

    // update own pharmacy user's
    public function update_user(Request $request,$id)
    {
            //Validate the request
        $validator = Validator::make($request->all(), [
            'firstName'=>'required',
            'lastName'=>'required',
            'role'=>'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        $user = PharmacyUser::find($id);
        $user->update([
            'firstName'=>$request->input('firstName'),
            'lastName'=>$request->input('lastName'),
            'role'=>$request->input('role'),
        ]);
        return response()->json(['msg'=>'User Updated Successfully']);
        }
    }
// password reset
// password reset
public function resetPassword(Request $request)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'password' => 'required|min:6', // Ensure a minimum length for security
        'phoneNumber' => 'required',
        'otp' => 'required|digits:4',
    ]);

    // Validate error message response
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $password = $request->input('password');
    $phoneNumber = $request->input('phoneNumber');
    $otp = $request->input('otp');

    // Retrieve the OTP record from the database
    $otpRecord = Otp::where('phoneNumber', $phoneNumber)->where('otp', $otp)->first();

    // Check if the OTP record exists and if it is still valid
    if ($otpRecord && $otpRecord->expire_at > now()) {
        $user = PharmacyUser::where('phoneNumber', $phoneNumber)->first();

        if (!$user) {
            return response()->json(['error' => 'No user exists with this phone number.'], 404);
        } else {
            $user->update(['password' => bcrypt($password)]);
            // Delete all OTP records for the matching phone number after password reset
            Otp::where('phoneNumber', $phoneNumber)->delete();
            return response()->json(['msg' => 'Password changed successfully.'], 200);
        }
    } else {
        // Delete OTP records after the invalid or expired OTP attempt
        Otp::where('phoneNumber', $phoneNumber)->delete();
        return response()->json(['error' => 'The provided OTP is invalid or expired. Please try again.'], 400);
    }
}

}
