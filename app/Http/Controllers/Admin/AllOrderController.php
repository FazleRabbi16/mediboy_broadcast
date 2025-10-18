<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Pharmacy;
use App\Models\UserAccount;
use App\Models\DeliveryTo;
use App\Models\OrderItem;
use App\Models\OrderPrescription;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AllOrderController extends Controller
{
     /*
     place order from admin
     */ 
     // serach user exist or not to 
     public function searchUser(Request $request)
     {
         $phoneNumber = $request->input('userNumber');
         $user = User::where('phoneNumber',$phoneNumber)->first();
         return response()->json([
         "userDetails" => $user
         ]);
     }
     // get pharmacies for place order
     public function getPharmacyForOrderPlace()
     {
        $pharmacies = Pharmacy::with('pharmacy_business')->get();
        return response()->json([
         "pharmacies" => $pharmacies
         ]);
     }
     // place order
     public function place_order(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            "user_id"=>'required|integer',
            "pharmacy_id"=>'required|integer',
            "type"=>'required',
            "area"=>'required',
            "orderItems" => 'required|array',
            "prescriptions" => 'nullable|array',
            "payment_method"=>'required',
            "offer_total_amount"=>'required',
            "status"=>'required',
            //delivery address
            "deliveryToFullName" => 'required',//reciver
            "deliveryFullAddress" => 'required',
            "deliveryToContact" => 'required',
            "deliveryToAddressType" => 'required',
            //delivery charge and cashback
            
            //another order required option
            'coupon' => 'nullable|string',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $user_id = $request->input('user_id');
            $pharmacy_id = $request->input('pharmacy_id');
            // Generate order number using timestamp and a sequential counter
            $timestamp = time(); // Get current timestamp (seconds since Unix epoch)
            $subStrTime = substr($timestamp, -3); // Extract last 6 digits of timestamp
            $length=3;
            $random_string = substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
            $orderNo=$random_string.$subStrTime;
            $type = $request->input('type');
            $area = $request->input('area');
            $payment_method = $request->input('payment_method');
            $offer_total_amount = $request->input('offer_total_amount');
            $status = $request->input('status');
            $deliveryCharge = $request->input('deliveryCharge');
            $offer_deliveryCharge = $request->input('offer_deliveryCharge');
            $offer_grandTotal = $request->input('offer_grandTotal');
            $comission_amount = $request->input('comission_amount');
            $delivery_confirmation_code = rand(1000, 9999);
            $orderDate = $request->input('order_date') ?? Carbon::now()->toDateString();//today date year-month-day formate
            $coupon = $request->input('coupon');
            // order products and prescriptions
            $prescription = $request->input('prescriptions');
            $order_products = $request->input('orderItems');
            
            // delivery address 
            $deliveryTo = $request->input('deliveryToFullName');
            $deliveryContact = $request->input('deliveryToContact');
            $deliveryAddress = $request->input('deliveryFullAddress');
            $addressType = $request->input('deliveryToAddressType');
            // cash-back amount
            $cashback = $request->input('cashback');
            // create order record
            $order= Order::create([
                'user_id' =>$user_id,
                'pharmacy_id' => $pharmacy_id,
                'orderNo' => $orderNo,
                'type' => $type,
                'area' => $area,
                'payment_method' => $payment_method,
                'offer_total_amount' => $offer_total_amount,
                'status' => $status,
                'deliveryCharge' => $deliveryCharge,
                'offer_deliveryCharge' => $offer_deliveryCharge,
                'offer_grandTotal' => $offer_grandTotal,
                'comission_amount' => $comission_amount,
                'orderDate' => $orderDate,
                'delivery_confirmation_code' => $delivery_confirmation_code,
                'coupon' => $coupon,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
            //get order id
            $order_id = $order->id;
            // cash back amount
            $orderCashback = UserAccount::updateOrCreate(
                                ['user_id' => $user_id],
                                [
                                    // when creating: default 0 + cashback
                                    'balance' => DB::raw('COALESCE(balance, 0) + ' . (float) $cashback),
                                ]
                            );
            // create delivery address 
            if(!empty($deliveryAddress)){
                DeliveryTo::create([
                    'order_id'=>$order_id,
                    'deliveryTo'=>$deliveryTo,
                    'deliveryContact'=>$deliveryContact,
                    'deliveryAddress'=>$deliveryAddress,
                    'addressType'=>$addressType,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }
            // create order items
            foreach ($order_products as $product) {
                // create order item
                OrderItem::create([
                    'order_id'           => $order_id,
                    'product_id'         => $product['product_id'],
                    'discount_unit_price'=> $product['discount_unit_price'],
                    'offer_unit_price'   => $product['offer_unit_price'],
                    'quantity'           => $product['orderQty'],
                    'total_price'        => $product['discount_unit_price']*$product['orderQty'],
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate
                ]);
                
            }
            if ($request->hasFile('prescriptions')) 
            {
            foreach ($request->file('prescriptions') as $file) {
                if ($file->isValid()) {
                    // Generate a unique filename
                    $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        
                    // Store the file in the 'prescription' folder
                    $file->storeAs('public/prescription', $filename);
        
                    // Create OrderPrescription record
                    OrderPrescription::create([
                        'order_id' => $order_id,
                        'prescription_copy' => $filename,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);
        
                    // Optional: copy to another folder
                    $destinationFolder = 'order_prescription';
                    Storage::disk('public')->copy('prescription/' . $filename, $destinationFolder . '/' . $filename);
                }
              }
            }
             return response()->json(["success"=>"Order Created successfully"]);
        }
    }
    
    public function fileUploadChecker(Request $request)
    {
    if ($request->hasFile('prescriptions')) {
    foreach ($request->file('prescriptions') as $file) {
        if ($file->isValid()) {
            // Generate a unique filename
            $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();

            // Store the file in the 'prescription' folder
            $file->storeAs('public/prescription', $filename);

            // Create OrderPrescription record
            OrderPrescription::create([
                'order_id' => 18,
                'prescription_copy' => $filename,
            ]);

            // Optional: copy to another folder
            $destinationFolder = 'order_prescription';
            Storage::disk('public')->copy('prescription/' . $filename, $destinationFolder . '/' . $filename);
        }
    }
 }
}

     public function order_highlite_info()
     {
       $statusCounts = Order::selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->get();
       return response()->json([
        "statusCounts"=>$statusCounts,
      ]);
     }
     //get all order for admin
     public function get_orders(Request $request)
     {
        $status = $request->input('status');
        if($status === 'All' || $status === 'all'){
         $allOrders = Order::with(['pharmacy:id,pharmacyName,proprietor,contact,division,district,upazilla,area,placeDetails,cover_image,googleLink','deliveryToAddress','orderPrescriptions.precription','orderItems.product:id,productName,genericName,retail_max_price,type,quantity,prescription,feature,coverImage','users'])->orderBy('id', 'desc')->paginate(10);
        }else {
         $allOrders = Order::with(['pharmacy:id,pharmacyName,proprietor,contact,division,district,upazilla,area,placeDetails,cover_image,googleLink','deliveryToAddress','orderPrescriptions.precription','orderItems.product:id,productName,genericName,retail_max_price,type,quantity,prescription,feature,coverImage','users'])->where('status',$status)->orderBy('id', 'desc')->paginate(10);
        }
        
       return $allOrders;
      }
     public function search(Request $request)
     {
         $id=$request->input('id');
         $Order = Order::with(['pharmacy:id,pharmacyName,proprietor,contact,division,district,upazilla,area,placeDetails,cover_image,googleLink','deliveryToAddress','orderPrescriptions.precription','orderItems.product:id,productName,genericName,retail_max_price,type,quantity,prescription,feature,coverImage','users'])->where('id',$id)->first();
          return $Order;
       }
     public function filter(Request $request)
     {
       $start_date = $request->input('start_date');
       $end_date = $request->input('end_date');
       $status = $request->input('status');
       $filterOrders = Order::with(['pharmacy:id,pharmacyName,googleLink','deliveryAddress','orderPrescriptions','orderItems', 'orderItems.product:id,productName,genericName,retail_max_price,retail_min_offer_price,unit_in_pack,type,quantity,prescription,feature','users'])
                       ->whereBetween('created_at',[$start_date,$end_date])
                       ->whereIn('status', $status)
                       ->orderBy('created_at', 'desc')
                       ->paginate(50);
        return $filterOrders;
    }
     // order by analysis first time
    public function orderAnalysis(Request $request)
    {
    $start_date = $request->input('start_date');
    $end_date = $request->input('end_date');
    $status = 'Delivered';

    $homeDeliveryOrders = Order::whereBetween('orderDate', [$start_date, $end_date])
                               ->where('status', $status)
                               ->where('type', 'home_delivery')
                               ->orderBy('orderDate', 'desc')
                               ->get()
                               ->map(function($order) {
                                   return [
                                       'id'=>$order->id,
                                       'product_price' => $order->offer_total_amount,
                                       'orderDate' => $order->orderDate,
                                       'offer_deliveryCharge' => $order->offer_deliveryCharge,
                                       'comission_amount' => $order->comission_amount,
                                   ];
                               });

    return response()->json([
        "home_delivery_orders" => $homeDeliveryOrders,
    ]);
}

   
}
