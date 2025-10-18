<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiderShift;
use App\Models\ActiveShift;
use App\Models\RiderShiftPlaceWithCommission;
use App\Models\Order;
use App\Models\MSale;
use App\Models\RiderDelivery;
use App\Models\RiderTransaction;
use App\Models\RiderWallet;
use Carbon\Carbon;
use Validator;
use Auth;

class RiderDeliveryController extends Controller
{
    //get active delivery/job for a rider
    public function activeJob()
    {
        $rider = Auth::guard('rider')->user();
        $rider_id = $rider->id;
        $currentDate=Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i:s');
        $activeShift = ActiveShift::where('rider_id',$rider_id)->whereDate('activeDateTime','=',$currentDate)->whereTime('givenEndTime','>',$currentTime)->first();
        if($activeShift === NULL)
        {
        return response()->json([
        'msg'=>'Your shift time is over.Please start shift to get more job'
        ]);
        }else if ($activeShift->is_online === 0){
        return response()->json([
        'msg'=>'Opps your are offline ! Back online to get job'
        ]);
        }else{
        $riderZoneDivision = $activeShift->division;
        $riderZoneDistrict = $activeShift->district;
        $riderZoneUpazilla = $activeShift->upazilla;
        // get rider comission
        $riderCommission= RiderShiftPlaceWithCommission::where('division',$riderZoneDivision)->where('district',$riderZoneDistrict)->where('upazilla',$riderZoneUpazilla)->first();
        // Combine the variables into an array and then use implode
        $riderZoneString = implode(',', [$riderZoneDivision,$riderZoneDistrict,$riderZoneUpazilla]);
        // get confirm order
        $orders = Order::with('deliveryToAddress')
        ->with('pharmacy')
        ->with('users')
        ->where('area', 'In-area')
        ->where('status', 'Confirmed')
        ->whereHas('deliveryToAddress', function ($query) use ($riderZoneString) {
            $query->where('deliveryAddress', 'like', '%' . $riderZoneString . '%');
        })
        ->get();
        return response()->json([
        'orders'=>$orders,
        'commission'=>$riderCommission,
        ]);
        }
    }
    // active job for delivery
    public function activeJobToDelivery(Request $request)
    {
    // Validate the request
    $validator = Validator::make($request->all(), [
        'order_id' => 'required',
    ]);

    // Validate error message response
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()]);
    } else {
        $order_id = $request->input('order_id');
        $order = Order::find($order_id);
        $rider = Auth::guard('rider')->user();
        $rider_id = $rider->id;

        $cod = $order->payment_method === 'COD' ? $order->offer_grandTotal : 0.00;
        $deliveryCharge = $order->deliveryCharge;
        $status = $rider->status;
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        $currentDate = Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i:s');

        $activeShift = ActiveShift::where('rider_id', $rider_id)
            ->whereDate('activeDateTime', '=', $currentDate)
            ->whereTime('givenEndTime', '>', $currentTime)
            ->first();

        if ($status === 'In-Active') {
            return response()->json([
                'msg' => 'Oops! You are an inactive rider. Please contact us if anything is wrong.'
            ]);
        } else if ($activeShift === NULL) {
            return response()->json([
                'msg' => 'Your shift time is over. Please start a shift to get more jobs.',
                'data' => $activeShift
            ]);
        } else if ($activeShift->on_delivery === 1) {
            return response()->json([
                'msg' => 'Please complete your current job first.'
            ]);
        }else if ($activeShift->is_online === 0) {
            return response()->json([
                'msg' => 'You are offline ! Please online to get the job.'
            ]);
        } else if ($order->status !== 'Confirmed') {
            return response()->json([
                'msg' => 'Oops! You are late to get this job. Already a rider has taken this job. Please go to the job board for updates on active jobs.'
            ]);
        } else {
            $riderZoneDivision = $activeShift->division;
            $riderZoneDistrict = $activeShift->district;
            $riderZoneUpazilla = $activeShift->upazilla;
            // Get rider commission
            $riderCommission = RiderShiftPlaceWithCommission::where('division', $riderZoneDivision)
                ->where('district', $riderZoneDistrict)
                ->where('upazilla', $riderZoneUpazilla)
                ->first();
            // Check if commission is found and has the correct property
            if ($riderCommission && isset($riderCommission->commission)) {
                $commissionAmount = round($deliveryCharge * ($riderCommission->commission / 100), 2);
            } else {
                return response()->json([
                    'msg' => 'Commission details not found for your zone.'
                ]);
            }

            $activeShift->update(['on_delivery' => 1]);
            $order->update(['status' => 'Processing']);
            RiderDelivery::create([
                'rider_id' => $rider_id,
                'order_id' => $order_id,
                'active_date_time' => $currentDateTime,
                'earn' => $commissionAmount,
                'cod' => $cod,
                'status' => 'Processing',
            ]);

            return response()->json([
                'msg' => 'Your job is active! Please check the active delivery section for future tasks.',
            ]);
        }
    }
}
    // show active delivery
    public function getActiveDelivery()
    {
        // Get the authenticated rider
        $rider = Auth::guard('rider')->user();
        // Define the statuses to filter
        $status = ['Processing', 'Pickup'];

        // Fetch the active delivery where the status is 'Processing' for the current rider
        $active_delivery = RiderDelivery::with([
            'orderDetails.pharmacy',
            'orderDetails.orderItems',
            'orderDetails.users',
            'orderDetails.orderItems.product:id,productName,genericName,retail_max_price,cart_qty_inc,cart_text,type,quantity,prescription,feature,status,coverImage,company_id,category_id',
            'orderDetails.orderItems.product.company','orderDetails.deliveryToAddress'])->where('rider_id', $rider->id)
                            ->whereIn('status',$status)
                            ->first();
        // Return response as JSON
        return response()->json([
         "id"=>$active_delivery
        ]);
    }

   // update status pickup & current time
    public function pickup(Request $request)
    {
       //Validate the request
       $validator = Validator::make($request->all(), [
        'order_id'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        $rider = Auth::guard('rider')->user();
        $rider_id = $rider->id;
        $order_id = $request->input('order_id');
        // find order
        $order=Order::find($order_id);
        $delivery_status = 'Processing';
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        $activeDelivery=RiderDelivery::where('rider_id',$rider_id)->where('order_id',$order_id)->where('status',$delivery_status)->first();
        if($order->status=="ReadyForPickup" && $activeDelivery!==NULL)
        {
        $activeDelivery->update(['status'=>'Pickup','pickup_date_time'=>$currentDateTime]);
        $order->update(['status'=>'On delivery']);
        return response()->json([
        'msg'=>'Pickup successfully now please delivery it as soon as possible to save life',
        'data'=>$activeDelivery
        ]);
        }else{
        return response()->json([
        'msg'=>'Order not prepare for pickup please try again latter or contact support',
        ]);  
        }
        

        }
    }
    // update delivery time to delivered
    public function delivered(Request $request)
    {
       //Validate the request
       $validator = Validator::make($request->all(), [
        'order_id'=> 'required',
        'dc_code'=> 'required'
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        $rider = Auth::guard('rider')->user();
        $rider_id = $rider->id;
        $order_id = $request->input('order_id');
        $dc_code = $request->input('dc_code');
        $order=Order::find($order_id);
        $delivery_status = 'Pickup';
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        $activeDelivery=RiderDelivery::where('rider_id',$rider_id)->where('order_id',$order_id)->where('status',$delivery_status)->first();
        $order=Order::where('id',$order_id)->where('delivery_confirmation_code',$dc_code)->first();
       
        if($activeDelivery!==NULL && $order !==NULL)
        {
        //calculate amount for transaction and wallet
        $activeDeliveryEarn=$activeDelivery->earn;
        $activeDeliveryCod=$activeDelivery->cod;
        $balance=$activeDeliveryEarn-$activeDeliveryCod;
        // create a transaction for cod delivery
        $transaction=RiderTransaction::create([
            'rider_id'=>$rider_id,
            'order_id'=>$order_id,
            'tnxType'=>'Collection',
            'tnxMedia'=>'COD',
            'tnxDateTime'=>$currentDateTime,
            'amount'=>-$activeDeliveryCod,
        ]);
        if(!$transaction)
        {
        return response()->json([
        'msg'=>'Transaction failed',
        ]);    
        }
        // update or create wallet for a rider
        $riderWallet=RiderWallet::where('rider_id',$rider_id)->first();
        if($riderWallet)
        {
         $newBalance=$riderWallet->balance+$balance;
         $walletUpdate = $riderWallet->update(['balance'=>$newBalance]);
        if(!$walletUpdate)
        {
        return response()->json([
        'msg'=>'Wallet updated failed',
        ]);
        }
        }else{
        $createWallete = RiderWallet::create([
         'rider_id'=>$rider_id,
         'balance'=>$balance
        ]);
        if(!$createWallete)
        {
        return response()->json([
        'msg'=>'Wallet create failed',
        ]);
        }
        }
        $activeShift = ActiveShift::where('rider_id',$rider_id)->where('on_delivery',1)->update(['on_delivery'=>0]);
        $activeDelivery->update(['status'=>'Delivered','delivered_date_time'=>$currentDateTime]);
        $order->update(['status'=>'Delivered']);
        MSale::where('order_id',$order->id)->update(['status'=>'Delivered']);
        return response()->json([
        'msg'=>'Delivered successfully ! you save a life',
        'data'=>$activeDelivery
        ]);
        }else{
        return response()->json([
        'msg'=>'Dc-code not match or Someting wrong please try again latter or contact support',
        ]);
        }
        }
    }
    // get last 30 days delivery history
    public function lastThirtyDelivery()
    {
       $rider = Auth::guard('rider')->user();
       $rider_id = $rider->id;
       $status=['Delivered'];
       $last30Records = RiderDelivery::with(['orderDetails','orderDetails.users','orderDetails.pharmacy'])->where('rider_id',$rider_id)->whereIn('status',$status)->orderBy('created_at', 'desc')->limit(30)->get();
       return response()->json([
        'data'=>$last30Records
       ]);
    }
    /*
    ------------------
    For Admin section
    ------------------
    */ 
    public function getDeliveryReportForAdmin(Request $request)
    {
      $startDate=$request->input('startDate');
      $endDate=$request->input('endDate');
      $deliveries = RiderDelivery::with(['orderDetails','rider'])->whereBetween('created_at', [$startDate, $endDate])->orderBy('created_at','desc')->get();
      return response()->json([
        'deliveries'=>$deliveries
      ]);
    }
    //search delivery to cancel via rider id 
    public function searchDelivery(Request $request)
    {
      $rider_id=$request->input('id');
      $delivery = RiderDelivery::with(['orderDetails','rider'])->where('rider_id',$rider_id)->orderBy('id','DESC')->get();
      return response()->json([
        'delivery'=>$delivery
      ]);
    }
    // cancel delivery
    public function cancelDelivery(Request $request)
    {
        $rider_id=$request->input('rider_id');
        $delivery_id=$request->input('id');
        $currentDateTiem=Carbon::now()->format('Y-m-d H:i:s');
        $currentDate = Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i:s');
        //active shift
        $activeShift = ActiveShift::where('rider_id', $rider_id)
            ->whereDate('activeDateTime', '=', $currentDate)
            ->whereTime('givenEndTime', '>', $currentTime)
            ->first();
        $delivery = RiderDelivery::find($delivery_id);
        $delivery->update(['status'=>'Cancel','cancel_date_time'=>$currentDateTiem]);
        $activeShift->update(['on_delivery'=>0]);
        // update order status to confirm for cancel delivery
        Order::where('id',$delivery->order_id)->update(['status'=>'Confirmed']);
        return response()->json([
        'msg'=>'Delivery Cancel successfully'
        ]);
    }
    /*
    1.Real-time update lgbe kon rider ka kon order assign kora hocce
       *kono rider jodi order cancel korte cai sai khatro toiri kora lgbe
       *cancel korte rider_id -> aita diye src krle assign order ta cole asbe tokhon cancel kore dite hobe
    ID
    Rider_id
    Order_id
    Pharmacy_id
    Rider Name
    Rider contact
    Status

    3.Delivery Report Date - Date
    4.Wallet balance clear korte - rider id dita hobe then amra balance clear krbo
    5.Real-time balance update ja kon rider er koto tk due ace . -jar beshi sa prothome sho hobe
       *In-active korte parbe admin sa jodi -tk er poriman besi din jomiye rakhe
       *call or what's app if rider has much - balance . call rider to clear it
    */ 
}
