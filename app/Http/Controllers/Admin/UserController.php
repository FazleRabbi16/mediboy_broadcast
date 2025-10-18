<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\UserAgreement;
use App\Models\UserAccount;
use App\Models\Prescription;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class UserController extends Controller
{
    public function add_user(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
        'firstName'=>'required',
        'lastName'=>'required',
        'phoneNumber'=>'required|unique:users',
        'bloodGroup'=>'required',
        'donateBlood'=>'required',
        'termsConditions'=>'required',
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
            'password' => bcrypt(rand(100000, 999999)),
            'division'=>$request->input('division'),
            'district'=>$request->input('district'),
            'upazilla'=>$request->input('upazilla'),
            'bloodGroup'=>$request->input('bloodGroup'),
            'donateBlood'=>$request->input('donateBlood'),
            'termsConditions'=>$request->input('termsConditions'),
            'status'=>"new",
        ]);
        $balance= UserAccount::create(['user_id'=>$user->id,'balance'=>0.00]);
        $response = [
            'message'=>"User created successfully",
            // 'token'=>$token
        ];
        return response($response,201);
        }
}
    public function user_header_highlite()
    {
        $statusCounts = User::selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->get();
        return $statusCounts;
    }
    public function get_users_details()
    {
        $users = User::withCount(['orders', 'prescriptions'])
             ->orderBy('id', 'DESC') // Order by id in descending order
             ->paginate(10);

        return $users;
    }
    public function get_user_prescriptions(Request $request)
    {
        $user_id=$request->input('user_id');
        $prescriptions = Prescription::where('user_id',$user_id)->get();
        return $prescriptions;
    }
    public function destroyMultiple(Request $request)
    {
        $prescriptions = Prescription::whereIn('id',$request->ids)->get();
        foreach ($prescriptions as $images) {
            $fileName = $images->image;
            if($fileName){
                Storage::delete("public/prescription/{$fileName}");
            }
        }
        Prescription::destroy($request->ids);
        return response()->json(['success'=>'Data Deleted Successfully']);
    }
    public function updateStatus(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $existUser = User::find($id);
        $existUser->update(['status'=>$status]);
        return response()->json(['success'=>'User Status Updated']);
    }
    public function search(Request $request)
    {
     $phoneNumber = $request->input('phoneNumber');
     $userId = User::where('phoneNumber', $phoneNumber)
                ->first();
     if(!$userId)
     {
        return response()->json([
            "msg"=>'User not exist'
        ]);
     }else{
        $user = User::withCount(['orders', 'prescriptions'])->where('phoneNumber',$phoneNumber)->first();
        return response()->json([
            "user"=>$user
        ]);
     }

    }
    public function filter(Request $request)
    {
      $status = $request->input('status');
      $users = User::withCount(['orders', 'prescriptions'])->whereIn('status', $status)->paginate(50);
      return $users;
    }
   /*
   -----------------
   User Agreement
   -----------------
   */
  public function set_user_agreement(Request $request)
  {
     $content = $request->input('content');
      $agreement_exist = UserAgreement::first();
      if($agreement_exist)
      {
       $agreement_exist->update(['content'=>$content]);
      }else{
        UserAgreement::create(['content'=>$content]);
      }
    return response()->json([
        'msg'=> "User Agreement Set Successfully",
    ]);
  }
  public function get_user_agreement()
  {
    $content = UserAgreement::get();
    return response()->json([
        'content'=>$content,
    ]);
  }
}
