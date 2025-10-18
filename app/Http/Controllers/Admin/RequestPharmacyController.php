<?php

namespace App\Http\Controllers\Admin;

use Validator;
use Illuminate\Http\Request;
use App\Models\RequestPharmacy;
use App\Http\Controllers\Controller;

class RequestPharmacyController extends Controller
{

    public function index()
    {
         $requestPharmacy = RequestPharmacy::all();
         return $requestPharmacy;
    }


    public function store(Request $request)
    {
        //requested pharmacy
        $validator = Validator::make($request->all(), [
            'fullName'=> 'required',
            'contact'=> 'required',
            'email'=> 'required',
            'pharmacyName'=> 'required',
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'place'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        //Get all text value
        $fullName = $request->input('fullName');
        $contact = $request->input('contact');
        $email = $request->input('email');
        $pharmacyName = $request->input('pharmacyName');
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $place = $request->input('place');
        $requestPharmacy=RequestPharmacy::create([
            'fullName'=>$fullName,
            'contact'=>$contact,
            'email'=>$email,
            'pharmacyName'=>$pharmacyName,
            'division'=>$division,
            'district'=>$district,
            'upazilla'=>$upazilla,
            'place'=>$place,
        ]);
        }
        return response()->json(['success'=>'Data Submited Successfully']);
    }


    public function search(Request $request)
    {
        $contact = $request->input('contact');
        // Find single pharmacy by contact number
        $singleRequestPharmacy = RequestPharmacy::where('contact', $contact)->first();
        return $singleRequestPharmacy;
    }

    /*
    -----------------------------------------
    Status update when admin update stage
    -----------------------------------------
     */
    public function update(Request $request)
    {
        // validation before update
        $validator = Validator::make($request->all(), [
            'id'=> 'required',
            'status'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        //Get all text value
        $rowId = $request->input('id');
        $status = $request->input('status');
        $existItem = RequestPharmacy::find($rowId);
        if($existItem){
           $existItem->update(['status'=>$status]);
          }
        }
        return response()->json(['success'=>'Data Updated Successfully']);
    }

    public function destroy($id)
    {
         //find single pharmacy
         $category = RequestPharmacy::find($id);
         $category->delete();
         return response()->json(['success'=>'Request Pharmacy Remove Successfully']);
    }
    // agreement update
   public function updateAgreement(Request $request)
   {
      // validation before update
      $validator = Validator::make($request->all(), [
        'id'=> 'required',
        'agreement'=> 'required',
    ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
    //Get all text value
    $rowId = $request->input('id');
    $agreement = $request->input('agreement');
    $existItem = RequestPharmacy::find($rowId);
    if($existItem){
       $existItem->update(['agreement'=>$agreement]);
      }
    }
    return response()->json(['success'=>'Agreement Data Submitted Successfully']);
   }
}
