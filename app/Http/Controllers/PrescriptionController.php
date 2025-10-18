<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use Auth;

class PrescriptionController extends Controller
{
// show all prescription of Auth User
public function index()
{
    $user_id=Auth::user()->id;
    $prescriptions = Prescription::where('user_id',$user_id)->orderBy('id','desc')->get();
    return $prescriptions;
}

//store multiple prescription
public function store(Request $request)
{
    //Validate the request
    $validator = Validator::make($request->all(), [
        'files' =>'required',
        'files.*'=>'image'

    ]);

    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
        $user_id=Auth::user()->id;
        if($request->hasFile('files')){
            $files = $request->file('files');
            //get each file to upload
            foreach ($files as $file) {
                //file extention
                $fileExt = $file->getClientOriginalExtension();
                //file name to store
                $fileNameToStore = rand(0,1999)."_".time().".".$fileExt;
                // store path
                $path = $file->storeAs('public/prescription',$fileNameToStore);
                Prescription::create([
                    'user_id'=>$user_id,
                    'image'=>$fileNameToStore
                ]);
            }
        }
        return response()->json(['success'=>'Data Submited Successfully']);
    }
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
public function removePresription($id){
    // Fetch the prescription
    $prescription = Prescription::find($id);
    if (!$prescription) {
        return response()->json(['error' => 'Prescription not found'], 404);
    }
    // Delete the associated image if it exists
    $fileName = $prescription->image;
    if ($fileName) {
        Storage::delete("public/prescription/{$fileName}");
    }
    // Delete the prescription
    $prescription->delete();
    return response()->json(['success' => 'Prescription deleted successfully']);
}
public function selectPrescription(Request $request)
{
    $prescriptions = Prescription::whereIn('id',$request->ids)->get();
    return $prescriptions;
}
// Upload and select recently uploaded prescription's
public function uploadSelect(Request $request)
{
    $user_id=Auth::user()->id;
    $ids = [];
    if($request->hasFile('files')){
        $files = $request->file('files');
        //get each file to upload
        foreach ($files as $file) {
            //file extention
            $fileExt = $file->getClientOriginalExtension();
            //file name to store
            $fileNameToStore = rand(0,1999)."_".time().".".$fileExt;
            // store path
            $path = $file->storeAs('public/prescription',$fileNameToStore);
           $prescription = Prescription::create([
                'user_id'=>$user_id,
                'image'=>$fileNameToStore
            ]);
           $ids[] = $prescription->id;
        }
      }
    $prescriptions = Prescription::whereIn('id',$ids)->get();
    return $prescriptions;
}

}
