<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use Auth;

class AdminAuthController extends Controller
{
    public function allUser()
    {
    $user = Auth::guard('admin')->user();
    return Admin::where('id', '!=', $user->id)->get();
    }
    // user login
    public function login(Request $request)
    {
         //Validate the request
         $validator = Validator::make($request->all(), [
            'email'=> 'required|email',
            'password'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $email = $request->input('email');
            $user = Admin::where('email',$email)->first();

            if (!$user || !Hash::check($request->input('password'), $user->password)) {
                        return response([
                            'msg' => 'incorrect email or password'
                        ], 401);
            }
            $token = $user->createToken('mediboy',['admin'])->plainTextToken;
            $response = [
                'user'=>$user,
                'token'=>$token
            ];
            return response($response,201);

        }
    }
    //create new user
    public function register(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            'firstName'=> 'required',
            'lastName'=> 'required',
            'email'=> 'required|unique:admins|email',
            'phoneNumber'=> 'required',
            'password'=>'required',
            'role'=>'required',
            'division'=>'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'donateBlood'=> 'required',
            'bloodGroup'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $admin= Admin::create([
                'firstName' => $request->input('firstName'),
                'lastName' =>$request->input('lastName'),
                'email' =>$request->input('email'),
                'password' =>bcrypt($request->input('password')),
                'role' =>$request->input('role'),
                'division' =>$request->input('division'),
                'district' => $request->input('district'),
                'upazilla' => $request->input('upazilla'),
                'bloodGroup' => $request->input('bloodGroup'),
                'donateBlood' => $request->input('donateBlood'),
                'termsConditions' => $request->input('termsConditions'),
                'phoneNumber' => $request->input('phoneNumber'),
            ]);
            $response = [
                'msg'=>'User Create Successfully'
            ];
            return response($response,201);
        }
    }

    // user details
    public function userDetails(){
        $user = Auth::guard('admin')->user();
        return response()->json(['data'=>$user]);
    }

  //update user profile
  public function updateProfile(Request $request)
  {
     //Validate the request
     $validator = Validator::make($request->all(), [
        'firstName'=>'required',
        'lastName'=>'required',
        'email'=>'required',
        'role'=>'required',
        'division'=>'required',
        'district'=>'required',
        'upazilla'=>'required',
        'bloodGroup'=>'required',
        'donateBlood'=>'required',
        'phoneNumber'=>'required',
    ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
      $user=Auth::guard('admin')->user();
      if (!$user) {
        return response([
            'msg' => 'User not exist'
        ], 401);
      }
      $user->update([
        'firstName'=>$request->input('firstName'),
        'lastName'=>$request->input('lastName'),
        'email'=>$request->input('email'),
        'division'=>$request->input('division'),
        'district'=>$request->input('district'),
        'subDistrict'=>$request->input('subDistrict'),
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
     $user=Auth::guard('admin')->user();
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

   public function logout(Request $request)
    {
        $user = Auth::guard('admin')->user();
        // Retrieve the token from the request or from the authenticated user's token
         $token = $user->currentAccessToken();
        // Revoke the specific token
        $token->delete();
        // You can perform additional logout actions if needed
        return response()->json(['message' => 'Successfully logged out']);
    }
  // remove a user
  public function removeUser(Request $request)
  {
     $id = $request->input('id');
     $adminUser=Admin::find($id);
     if($adminUser->role == "Admin")
     {
      return response()->json(['msg'=>'An admin can not be remove']);
     }else{
      $adminUser->delete();
      return response()->json(['msg'=>'User remove successfully']);
     }
  }
}
