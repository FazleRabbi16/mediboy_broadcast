<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\RiderTransaction;
use App\Models\RiderWallet;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Carbon\Carbon;


class RiderTransactionController extends Controller
{
    //get rider recent transaction 
    public function recentTransactionAndwalletBallance()
    { 
      $rider=Auth::guard('rider')->user();
      $rider_id = $rider->id;
      $last30Transaction=RiderTransaction::where('rider_id',$rider_id)->orderBy('id','DESC')->limit(30)->get();
      $wallet=RiderWallet::where('rider_id',$rider_id)->first();
      return response()->json([
        'transaction'=>$last30Transaction,
        'wallet'=>$wallet,
      ]);
    }
    /*
    ----------------------------------------
    Transaction and wallet report for Admin
    ----------------------------------------
    */ 
    public function riderWallet()
    {
      $riderWallets = RiderWallet::with('rider')->orderByRaw('balance < 0 DESC, ABS(balance) DESC')->paginate(100);
      return response()->json([
       'wallets'=>$riderWallets
      ]);
    }
    public function makeTransaction(Request $request)
    {
      //Validate the request
      $validator = Validator::make($request->all(), [
        'rider_id'=> 'required',
        'tnxType'=> 'required',
        'tnxMedia'=> 'required',
        'sender'=> 'required',
        'receiver'=> 'required',
        'amount'=> 'required',
      ]);
      // validate error message response
      if ($validator->fails()) {
          return response()->json(['errors'=>$validator->errors()]);
      }else{
        $rider_id=$request->input('rider_id');
        $tnxType=$request->input('tnxType');
        $tnxMedia=$request->input('tnxMedia');
        $sender=$request->input('sender');
        $receiver=$request->input('receiver');
        $tnxId=$request->input('tnxId');
        $amount=$request->input('amount');
        $tnxAmount;
        if($tnxType==='Cash-In')
        {
        $tnxAmount=$amount;
        }else if($tnxType==='Cash-Out')
        {
        $tnxAmount=-$amount;
        }
        $currentDateTime=Carbon::now()->format('Y-m-d H:i:s');
        $transaction = RiderTransaction::create([
          'rider_id'=>$rider_id,
          'tnxType'=>$tnxType,
          'tnxDateTime'=>$currentDateTime,
          'tnxMedia'=>$tnxMedia,
          'sender'=>$sender,
          'receiver'=>$receiver,
          'tnxId'=>$tnxId,
          'amount'=>$tnxAmount,
        ]);
        $riderWallet=RiderWallet::where('rider_id',$rider_id)->first();
        if($riderWallet)
        {
        $riderCurrentBalance=$riderWallet->balance;
        $riderUpdateBalance=$riderCurrentBalance+$tnxAmount;
        $riderWallet->update(['balance'=>$riderUpdateBalance]);
        }
        if(!$transaction)
        {
        return response()->json([
        'msg'=>'Transaction faild'
        ]);
        }else{
        return response()->json([
        'msg'=>'Transaction successfully done',
        ]);
        }

      }
    }
    public function searchWallet(Request $request)
    {
     $rider_id=$request->input('id');
     $riderWallet = RiderWallet::with('rider')->where('rider_id',$rider_id)->first();
      return response()->json([
       'riderWallet'=>$riderWallet
      ]);
    }
    public function getRiderTnx(Request $request)
    {
      $startDate=$request->input('startDate');
      $endDate=$request->input('endDate');
      $rider_id=$request->input('rider_id');//optional
      $transaction;
      if($rider_id)
      {
       $transaction=RiderTransaction::whereBetween('tnxDateTime',[$startDate,$endDate])->where('rider_id',$rider_id)->orderBy('tnxDateTime','DESC')->get();
      }else{
      $transaction=RiderTransaction::whereBetween('tnxDateTime',[$startDate,$endDate])->orderBy('tnxDateTime','DESC')->get();
      }
      return response()->json([
      'transaction'=>$transaction,
      ]);
    }
}
