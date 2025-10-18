<?php

namespace App\Http\Controllers;

use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Pharmacy;
use App\Models\OrderItem;
use App\Models\ReportBox;
use App\Models\CurrentStock;
use Illuminate\Http\Request;
use App\Models\DeliveryCharge;
use App\Models\DeliveryAddress;
use App\Models\OrderPrescription;
use App\Models\PickupAddress;
use App\Models\CancelOrderDetails;
use App\Models\MSale;
use App\Models\ActiveDeliveryAddress;
use App\Models\DefaultOrFreeDelivery;
use App\Models\PharmacyBusinessSetup;
use App\Models\StockProduct;
use App\Models\DeliveryTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class OrderController extends Controller
{

    // Fetch all order for user
    public function get_my_order()
    {
        $user_id = Auth::user()->id;
        $myOrders = Order::with(['pharmacy:id,pharmacyName,googleLink','orderItems','orderItems.product:id,productName,genericName,retail_max_price,cart_qty_inc,cart_text,type,quantity,prescription,feature,status,coverImage,company_id,category_id','orderItems.product.company','cancelData','orderPrescriptions','deliveryToAddress'])
        ->where('user_id',$user_id)
        ->OrderBy('id','DESC')
        ->get();
       return $myOrders;
    }
   

    public function OrderHelper()
    {
        $user_id = Auth::user()->id;
        //Set delivery charge
        $active_delivery_address=ActiveDeliveryAddress::where('user_id',$user_id)->first();
        $deliver_address_id = $active_delivery_address->delivery_address_id;
        $address=DeliveryAddress::find($deliver_address_id);
        $division=$address->division;
        $district=$address->district;
        // return $district;exit();
        $delivery_charege = DeliveryCharge::
                            where('division',$division)
                            ->where('district',$district)
                            ->get();
        $default_up_buy_delivery_charge = DefaultOrFreeDelivery::first();
        return response()->json([
            "delivery_charege"=>$delivery_charege,
            "default_up_buy_delivery_charge"=>$default_up_buy_delivery_charge,
          ]);
    }

    public function place_order(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            "pharmacy_id"=>'required|integer',
            "type"=>'required',
            "area"=>'required',
            "order_products" => 'required|array',
            "prescription" => 'nullable|array',
            "payment_method"=>'required',
            //for fetch delivery charge 
            "deliveryAddress" => 'required',
            "deliveryTo" => 'required',//reciver
            "deliveryContact" => 'required',
            "addressType" => 'required',
            //another order required option
            'coupon' => 'nullable|string',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $user_id = Auth::user()->id;
            $pharmacy_id = $request->input('pharmacy_id');
            $type = $request->input('type');
            $area = $request->input('area');
            $order_products = $request->input('order_products');
            $prescription = $request->input('prescription');
            $payment_method = $request->input('payment_method');
            $orderDate = Carbon::now()->toDateString();//today date year-month-day formate
            $status ="Pending";
            $coupon = $request->input('coupon');
            $delivery_confirmation_code = rand(1000, 9999);
            // delivery address 
            $deliveryAddress = $request->input('deliveryAddress');
            $deliveryTo = $request->input('deliveryTo');
            $deliveryContact = $request->input('deliveryContact');
            $addressType = $request->input('addressType');
            // Generate order number using timestamp and a sequential counter
            $timestamp = time(); // Get current timestamp (seconds since Unix epoch)
            $subStrTime = substr($timestamp, -3); // Extract last 6 digits of timestamp
            $length=3;
            $random_string = substr(str_shuffle(str_repeat($x='0123456789', ceil($length/strlen($x)) )),1,$length);
            $orderNo=$random_string.$subStrTime;
            
            //fetch delivery charge
            $delivery_address_parts = explode(',', $deliveryAddress);
            // Trim spaces
            $division = trim($delivery_address_parts[0] ?? '');
            $district = trim($delivery_address_parts[1] ?? '');
            $upazila  = trim($delivery_address_parts[2] ?? '');
            $delivery_charge_record = DeliveryCharge::where('division', $division)
                                      ->where('district', $district)
                                      ->where('upazilla', $upazila)
                                      ->first();
            $deliveryCharge = $delivery_charge_record ? $delivery_charge_record->delivery_charge : 0;
            // Get product IDs from order
            $productIds = array_column($order_products, 'product_id');
            
            // Fetch stock data for only those products
            $pharmacies = CurrentStock::where('pharmacy_id', $pharmacy_id)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id'); // so we can access by product_id
            
            $sum_discount_price = 0;
            $sum_mediboy_offer_price = 0;
            
            foreach ($order_products as $order) {
                $productId = $order['product_id'];
                $qty = $order['quantity'];
            
                if (isset($pharmacies[$productId])) {
                    $stock = $pharmacies[$productId];
                    $sum_discount_price      += $stock->discount_price * $qty;
                    $sum_mediboy_offer_price += $stock->mediboy_offer_price * $qty;
                }
            }
            
            $differ_btwn_price = $sum_discount_price - $sum_mediboy_offer_price;
            
            // 90% of comission amount will expense for delivery charge
            if (($differ_btwn_price * 0.9) > $deliveryCharge) {
                $offer_delivery_charge = 0;
            } else {
                $offer_delivery_charge = $deliveryCharge;
            }
            
            $offer_total_amount = $sum_discount_price;
            $offer_grandTotal = $sum_discount_price+$offer_delivery_charge;
            $offer_deliveryCharge = $offer_delivery_charge;
            $comission_amount = $differ_btwn_price;
            // create order record
            $order= Order::create([
                'user_id' =>$user_id,
                'pharmacy_id' => $pharmacy_id,
                'orderNo' => $orderNo,
                'type' => $type,
                'area' => $area,
                'offer_total_amount' => $offer_total_amount,
                'deliveryCharge' => $deliveryCharge,
                'comission_amount' => $comission_amount,
                'offer_deliveryCharge' => $offer_deliveryCharge,
                'offer_grandTotal' => $offer_grandTotal,
                'payment_method' => $payment_method,
                'status' => $status,
                'orderDate' => $orderDate,
                'delivery_confirmation_code' => $delivery_confirmation_code,
                'coupon' => $coupon,
            ]);
            //get order id
            $order_id = $order->id;
            // create delivery address 
            if(!empty($deliveryAddress)){
                DeliveryTo::create([
                    'order_id'=>$order_id,
                    'deliveryTo'=>$deliveryTo,
                    'deliveryContact'=>$deliveryContact,
                    'deliveryAddress'=>$deliveryAddress,
                    'addressType'=>$addressType
                ]);
            }
            // create order items
            foreach ($order_products as $product) {
                $productId = $product['product_id'];    // use $product, not $order
                $qty = $product['quantity'];
                if (isset($pharmacies[$productId])) {
                    $stock = $pharmacies[$productId];
                    $item_discount_total = $stock->discount_price * $qty;
            
                    // create order item
                    OrderItem::create([
                        'order_id'           => $order_id,
                        'product_id'         => $productId,
                        'discount_unit_price'=> $stock->discount_price,
                        'offer_unit_price'   => $stock->mediboy_offer_price,
                        'quantity'           => $qty,
                        'total_price'        => $item_discount_total
                    ]);
                }
            }

          if(!empty($prescription)){
            foreach ($prescription as $item) {
             // Extract filename from URL
             $filename = $item['image'];
                OrderPrescription::create([
                        'order_id'=>$order_id,
                        'prescription_copy'=>$filename,
                    ]);
            // Copy file to Order_prescription folder
            $sourceFilePath = Storage::disk('public')->path('prescription/'.$filename);
            $destinationFolder = 'order_prescription';
            Storage::disk('public')->copy('prescription/'.$filename, $destinationFolder.'/' .$filename);
              }
          }
        
        // remove all carts item after place order
         Cart::where('user_id',$user_id)->delete();
         return response()->json(["success"=>"Order Created Successfully"]);
        }
    }
     //update order status
     public function updateOrderStatus(Request $request)
    {
      //Validate the request
    $validator = Validator::make($request->all(), [
        "id"=>'required',
        "status"=>'required',
    ]);
     // validate error message response
     if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
      $rowId = $request->input('id');
      $status = $request->input('status');
      $existOrder = Order::find($rowId);
      $existOrder->update(['status'=>$status]);
      MSale::where('order_id',$rowId)->update(['status'=>'Delivered']);
      return response()->json(['msg' => "Order {$status} successfully"]);
      }
    }
    // get all order for select cancel
    public function get_modify_order()
    {
        $user_id = Auth::user()->id;
        $except=['cancel','delivered'];
        $toModifyOrder = Order::where('user_id',$user_id)->whereNotIn('status',$except)->get();
        return $toModifyOrder;
    }
    // get all order for report selector
    public function get_report_order()
    {
        $user_id = Auth::user()->id;
        $except=['cancel'];
        $toModifyOrder = Order::select('id')->where('user_id',$user_id)->whereNotIn('status',$except)->get();
        return $toModifyOrder;
    }

    public function set_cancel_order(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            "id"=>'required',
            "cancel_by"=>'required',
        ]);
         // validate error message response
         if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
           $order_id = $request->input('id');
           $status = 'Cancelled';
           $cancel_by=$request->input('cancel_by');
           // update order table status
           $existOrder = Order::find($order_id);
           $existOrder->update(['status'=>$status]);
           $cancelOrder = CancelOrderDetails::updateOrCreate(
            ['order_id' => $order_id], // Conditions to find the record
            ['cancel_by' => $cancel_by] // Data to update or create if not found
           );
            return response()->json(["msg"=>"Order cancelled successfully"]);
        }
     }
     public function set_report_order(Request $request)
     {
        //Validate the request
        $validator = Validator::make($request->all(), [
            "order_id"=>'required',
            "subject"=>'required',
            "reason"=>'required',
            "report_by"=>'required',
        ]);
         // validate error message response
         if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $order_id = $request->input('order_id');
            $subject = $request->input('subject');
            $reason = $request->input('reason');
            $report_by=$request->input('report_by');
            $cancelOrder= ReportBox::create([
                'order_id'=>$order_id,
                'subject'=>$subject,
                'reason'=>$reason,
                'report_by'=>$report_by
            ]);
            return response()->json(["msg"=>"Data Submitted Successfully"]);
         }
      }

/*
...................................
 Find pharmacy to place order
-----------------------------------
 */
// Find pharmacy in area for delivery to
public function find_pharmacy_in_area(Request $request)
{
    // Validate request
    $validator = Validator::make($request->all(), [
        "division" => 'required',
        "district" => 'required',
        "upazilla" => 'required',
        "order_product" => 'required|array',
        "order_product.*.product_id" => 'required|integer',
        "order_product.*.quantity" => 'required|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $division = $request->input('division');
    $district = $request->input('district');
    $upazilla = $request->input('upazilla');
    $order_product = $request->input('order_product');

    // Get delivery charge for the area
    $delivery_charge_record = DeliveryCharge::where('division', $division)
        ->where('district', $district)
        ->where('upazilla', $upazilla)
        ->first();

    $delivery_charge_value = $delivery_charge_record ? $delivery_charge_record->delivery_charge : 0;

    // Get active pharmacies in area
    $pharmacyIds = Pharmacy::where('division', $division)
        ->where('district', $district)
        ->where('upazilla', $upazilla)
        ->whereHas('pharmacy_business', function ($query) {
            $query->where('status', 'active');
        })
        ->pluck('id')
        ->toArray();

    // Get stocks for these pharmacies
    $pharmacies = CurrentStock::whereIn('pharmacy_id', $pharmacyIds)
        ->get()
        ->groupBy('pharmacy_id')
        ->filter(function ($stocks) use ($order_product) {
            // Keep only pharmacies that have all ordered products in sufficient quantity
            foreach ($order_product as $item) {
                $stock = $stocks->firstWhere('product_id', $item['product_id']);
                if (!$stock || $stock->in_stock < $item['quantity']) {
                    return false;
                }
            }
            return true;
        })
        ->map(function ($stocks) use ($order_product, $delivery_charge_value) {
            $sum_discount_price = 0;
            $sum_offer_price = 0;

            foreach ($order_product as $item) {
                $stock = $stocks->firstWhere('product_id', $item['product_id']);
                $sum_discount_price += $stock->discount_price * $item['quantity'];
                $sum_offer_price += $stock->mediboy_offer_price * $item['quantity'];
            }

            $difference_price = $sum_discount_price - $sum_offer_price;

            // Attach values to the Pharmacy model
            $pharmacy = $stocks->first()->pharmacy; // assumes relation to Pharmacy
            $pharmacy->sum_discount_price = $sum_discount_price;
            // $pharmacy->sum_offer_price = $sum_offer_price;
            // $pharmacy->difference_price = 50;

            // 90 % of comission amount will expense for delivery charge
            if (($difference_price * 0.9) > $delivery_charge_value) {
                $pharmacy->offer_delivery_charge = 0;
            } else {
                $pharmacy->offer_delivery_charge = $delivery_charge_value;
            }

            return $pharmacy;
        })
        ->values(); // reset keys

    return response()->json([
        'pharmacies' => $pharmacies,
    ]);
}
public function self_pickup_pharmacy(Request $request)
{
    // Validate request
    $validator = Validator::make($request->all(), [
        "division" => 'required',
        "district" => 'required',
        "upazilla" => 'required',
        "area" => 'required',
        "order_product" => 'required|array',
        "order_product.*.product_id" => 'required|integer',
        "order_product.*.quantity" => 'required|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $division = $request->input('division');
    $district = $request->input('district');
    $upazilla = $request->input('upazilla');
    $area = $request->input('area');
    $order_product = $request->input('order_product');

    // Get active pharmacies in area
    $pharmacyIds = Pharmacy::where('division', $division)
        ->where('district', $district)
        ->where('upazilla', $upazilla)
        ->where('area', $area)
        ->whereHas('pharmacy_business', function ($query) {
            $query->where('status', 'active');
        })
        ->pluck('id')
        ->toArray();

    // Get stocks for these pharmacies
    $pharmacies = CurrentStock::whereIn('pharmacy_id', $pharmacyIds)
        ->get()
        ->groupBy('pharmacy_id')
        ->filter(function ($stocks) use ($order_product) {
            // Keep only pharmacies that have all ordered products in sufficient quantity
            foreach ($order_product as $item) {
                $stock = $stocks->firstWhere('product_id', $item['product_id']);
                if (!$stock || $stock->in_stock < $item['quantity']) {
                    return false;
                }
            }
            return true;
        })
        ->map(function ($stocks) use ($order_product) {
            $sum_discount_price = 0;
            $sum_offer_price = 0;

            foreach ($order_product as $item) {
                $stock = $stocks->firstWhere('product_id', $item['product_id']);
                $sum_discount_price += $stock->discount_price * $item['quantity'];
                $sum_offer_price += $stock->mediboy_offer_price * $item['quantity'];
            }
            // Attach values to the Pharmacy model
            $pharmacy = $stocks->first()->pharmacy; // assumes relation to Pharmacy
            $pharmacy->sum_discount_price = $sum_discount_price;
            $pharmacy->offer_delivery_charge = 0;
            return $pharmacy;
        })
        ->values(); // reset keys

    return response()->json([
        'pharmacies' => $pharmacies,
    ]);
}

}
