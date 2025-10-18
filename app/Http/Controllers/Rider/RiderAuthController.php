<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Rider;
use App\Models\Otp;
use App\Models\RiderFile;
use App\Models\RiderWallet;
use Illuminate\Support\Facades\Storage;
use Validator;
use Auth;

class RiderAuthController extends Controller
{
    //add a new rider by admin
    public function addRider(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            'firstName'=> 'required',
            'lastName'=> 'required',
            'contact'=> 'required|unique:riders,contact',
            'email'=> 'required|email|unique:riders,email',
            'vehicle'=> 'required',
            'currAdd_division'=> 'required',
            'currAdd_district'=> 'required',
            'currAdd_upazilla' =>'required',
            'perAdd_division' =>'required',
            'perAdd_district'=> 'required',
            'perAdd_upazilla'=> 'required',
            'eighteenPlus'=> 'required',
            'ref_id'=> 'nullable|exists:riders,id',
            'agreementSigned'=> 'required',
            'profileImage'=>'image|required',
            'files.*'=>'image|required'
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
          //Get all text value
          $firstName = $request->input('firstName');
          $lastName = $request->input('lastName');
          $contact = $request->input('contact');
          $email = $request->input('email');
          $vehicle = $request->input('vehicle');
          $currAdd_division = $request->input('currAdd_division');
          $currAdd_district = $request->input('currAdd_district');
          $currAdd_upazilla = $request->input('currAdd_upazilla');
          $perAdd_division = $request->input('perAdd_division');
          $perAdd_district = $request->input('perAdd_district');
          $perAdd_upazilla = $request->input('perAdd_upazilla');
          $eighteenPlus = $request->input('eighteenPlus');
          $agreementSigned = $request->input('agreementSigned');
          $ref_id = $request->input('ref_id');
          $status="Active";
          $password=mt_rand(100000,999999);
          $profileFileNameToStore=NULL;
          // cover image upload
          if ($request->hasFile('profileImage')) {
            $profileImage = $request->file('profileImage');
            //file extention
            $fileExt = $profileImage->getClientOriginalExtension();
            //file name to store
            $profileFileNameToStore = rand(0,1999)."_".time().".".$fileExt;
            // store path
            $path = $profileImage->storeAs('public/rider_profile_image',$profileFileNameToStore);
            }
            $rider= Rider::create([
                'firstName' =>$firstName,
                'lastName' => $lastName,
                'contact' => $contact,
                'email' => $email,
                'password'=>bcrypt($password),
                'vehicle' => $vehicle,
                'currAdd_division' => $currAdd_division,
                'currAdd_district' => $currAdd_district,
                'currAdd_upazilla' => $currAdd_upazilla,
                'perAdd_division' => $perAdd_division,
                'perAdd_district' => $perAdd_district,
                'perAdd_upazilla' => $perAdd_upazilla,
                'eighteenPlus' => $eighteenPlus,
                'agreementSigned' => $agreementSigned,
                'ref_id' => $ref_id,
                'status' => $status,
                'profileImage' => $profileFileNameToStore,
            ]);
            // get current product id
            $rider_id = $rider->id;
            // rider wallet
            $riderWallet=RiderWallet::create([
            'rider_id'=>$rider_id,
            'balance'=>0.00
            ]);
            // extra file of riders
            if($request->hasFile('files')){
                $files = $request->file('files');
                //get each file to upload
                foreach ($files as $file) {
                    //file extention
                    $fileExt = $file->getClientOriginalExtension();
                    //file name to store
                    $fileNameToStore = rand(0,1999)."_".time().".".$fileExt;
                    // store path
                    $path = $file->storeAs('public/rider_files',$fileNameToStore);
                    RiderFile::create([
                        'file'=>$fileNameToStore,
                        'rider_id'=>$rider_id
                    ]);
                }
            }
        }
        return response()->json([
        'msg'=>"Rider added successfully"
        ]);
    }
    //Login rider
    public function login(Request $request)
    {
         //Validate the request
         $validator = Validator::make($request->all(), [
            'contact'=> 'required',
            'password'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $contact = $request->input('contact');
            $user = Rider::where('contact',$contact)->first();
   
            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                        return response([
                            'msg' => 'incorrect phone number or password'
                        ], 401);
            }
            $token = $user->createToken('mediboy',['rider'])->plainTextToken;
            $response = [
                'user'=>$user,
                'token'=>$token
            ];
        return response($response,201);
   
        }
    }
    // Update auth rider password
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
        $user=Auth::guard('rider')->user();
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
    // update auth rider profile image
    public function updateProfileImage(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            'profileImage'=>'image|required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        $rider=Auth::guard('rider')->user();
        $id = $rider->id;
        $coverFileNameToStore=$rider->profileImage;
        //product cover image update
        if($request->hasFile('profileImage')){
            if($rider->profileImage !=NULL){
              Storage::delete("public/rider_profile_image/{$rider->profileImage}");
            }
          $profileImage = $request->file('profileImage');
          //file extention
          $fileExt = $profileImage->getClientOriginalExtension();
          //file name to store
          $coverFileNameToStore = rand(0,1999)."_".time().".".$fileExt;
          // store path
          $path = $profileImage->storeAs('public/rider_profile_image',$coverFileNameToStore);
        }
        //update product table
        Rider::where('id',$id)->update([
            'profileImage' => $coverFileNameToStore,
        ]);
        }
        return response()->json([
            'msg'=>'Profile image update successfully'
        ]);
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
        $user = Rider::where('contact',$phoneNumber)->first();
        if (!$user) {
        return response(['error' => 'No user exist']);
        }else{
          $user->update(['password'=>bcrypt($password)]);
          // Delete all OTP records for the matching phone number after password reset
        //   Otp::where('phoneNumber', $phoneNumber)->delete();
          return response(['msg' => 'Successfully password change']);  
        }
        } else {
        // Delete all OTP records for the matching phone number after password reset
        //  Otp::where('phoneNumber', $phoneNumber)->delete();
        // OTP is invalid or expired
        return response()->json(['error' => 'Time over for this given otp,please try again by refresh the page']);
        }
        
    }
    public function details()
     {
        $user = Auth::guard('rider')->user();
        return response()->json(['data'=>$user]);
     }
    public function logout(Request $request)
    {

        $user = Auth::guard('rider')->user();
        // Retrieve the token from the request or from the authenticated user's token
         $token = $user->currentAccessToken();
        // Revoke the specific token
        $token->delete();
        // You can perform additional logout actions if needed
        return response()->json(['message' => 'Successfully logged out']);
    }
}
