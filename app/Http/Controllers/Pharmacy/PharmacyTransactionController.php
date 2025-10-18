<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PharmacyTransaction;
use Validator;
use Carbon\Carbon;

class PharmacyTransactionController extends Controller
{
    /*
    -------------------------
    Admin section function
    -------------------------
    */
    public function makePharmacyTransaction(Request $request)
    {
           //Validate the request
        $validator = Validator::make($request->all(), [
            'pharmacy_id'=>'required',
            'TnxType'=>'required',
            'TnxMedia'=>'required',
            'sender'=>'required',
            'receiver'=>'required',
            'amount'=>'required',
        ]);
        // validate error message response
        if ($validator->fails())
        {
        return response()->json(['errors'=>$validator->errors()]);
        }else{
        $currentDateTime=Carbon::now()->format('Y-m-d H:i:s');
        $pharmacy_id=$request->input('pharmacy_id');
        $TnxType=$request->input('TnxType');
        $TnxMedia=$request->input('TnxMedia');
        $sender=$request->input('sender');
        $receiver=$request->input('receiver');
        $amount=$request->input('amount');
        $tnx=PharmacyTransaction::create([
        'pharmacy_id'=>$pharmacy_id,
        'TnxDateTime'=>$currentDateTime,
        'TnxType'=>$TnxType,
        'TnxMedia'=>$TnxMedia,
        'sender'=>$sender,
        'receiver'=>$receiver,
        'amount'=>$amount,
        ]);
        return response()->json([
        'msg'=>'Trasaction successfully done'
        ]);
        }
    }
    public function PharmacyTransaction(Request $request)
    {
     $pharmacy_id=$request->input('pharmacy_id');
     $totalTnxSend=PharmacyTransaction::where('TnxType','send')->where('pharmacy_id',$pharmacy_id)->sum('amount');
     $totalTnxRec=PharmacyTransaction::where('TnxType','receive')->where('pharmacy_id',$pharmacy_id)->sum('amount');
     $tnx=PharmacyTransaction::where('pharmacy_id',$pharmacy_id)->orderBy('TnxDateTime','DESC')->paginate(5);
     return response()->json([
     'tnx'=>$tnx,
     'totalsend'=>$totalTnxSend,
     'totalrec'=>$totalTnxRec
     ]);
     
    }
}
