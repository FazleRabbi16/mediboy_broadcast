<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\FileUpload;
use App\Models\Software;
use App\Models\Tutorial;
use Illuminate\Http\Request;
use Validator;

class AdminFileUploadController extends Controller
{
   //get software
   public function getSoftware()
   {
     $software=Software::all();
     // Return the response with the file's path or any other data you need
     return response()->json(['software' =>$software]); 
   }
    //file upload
    public function addSoftwareUrl(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            'os'=> 'required',
            'user'=> 'required',
            'app_url'=> 'required'
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
         $os=$request->input('os');
         $app_url=$request->input('app_url');
         $user=$request->input('user');
         // app upload
          $softwareUpload =  Software::updateOrCreate(
            ['user'=>$user,'os' =>$os],
            [
                'os' => $os,
                'user' => $user,
                'app_url' => $app_url
            ]);
         if ($softwareUpload){
             // Return the response with the file's path or any other data you need
            return response()->json(['msg' => 'File added successfully',]);
        }else {
           // Return the response with the file's path or any other data you need
           return response()->json(['msg' => 'Failed to add file']);  
        }
        }
    }
    //remove software url
   public function removeSoftware(Request $request)
   {
     $id=$request->input('id');
     $software=Software::find($id);
     if($software)
     {
       $software->delete();
       return response()->json(['msg' =>'Item remove successfully']); 
     }else{
     // Return the response with the file's path or any other data you need
     return response()->json(['msg' =>'Failed to item remove']); 
     }
   }
   
  //fetch all tutorial video Desc order
  public function getTutorial()
  {
    $tutorial=Tutorial::all();
    // Return the response with the file's path or any other data you need
    return response()->json(['tutorial' =>$tutorial]);    
  }
  //add tutorial video
  public function addTutorial(Request $request)
  {
        //Validate the request
        $validator = Validator::make($request->all(), [
            'title'=> 'required',
            'url'=> 'required'
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
         $title=$request->input('title');
         $url=$request->input('url');
         // app upload
          $tutorialUpload =  Tutorial::Create(
            [
                'title' => $title,
                'url' => $url
            ]);
         if ($tutorialUpload){
             // Return the response with the file's path or any other data you need
            return response()->json(['msg' => 'File added successfully',]);
        }else {
           // Return the response with the file's path or any other data you need
           return response()->json(['msg' => 'Failed to add file']);  
        }
        }
    }
  //remove any tutorial video
  public function removeTutorial(Request $request)
  {
     $id=$request->input('id');
     $tutorial=Tutorial::find($id);
     if($tutorial)
     {
       $tutorial->delete();
       return response()->json(['msg' =>'Item remove successfully']); 
     }else{
     // Return the response with the file's path or any other data you need
     return response()->json(['msg' =>'Failed to item remove']); 
     }
  }
}
