<?php

namespace App\Http\Controllers\Pharmacy;

use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\MSale;
use App\Models\Order;
use App\Models\Stock;
use App\Models\OffSale;
use App\Models\Product;
use App\Models\MSaleItem;
use App\Models\OffSaleItem;
use App\Models\PharmacyAccount;
use App\Models\CurrentStock;
use App\Models\StockProduct;
use Illuminate\Http\Request;
use App\Models\OpenProductBatch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PharmacyBusinessSetup;

class SaleController extends Controller
{
    /*
    -----------------
    Offline Sale 
    -----------------
    */ 
// product details for cart add
public function off_productDetails(Request $request)
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $product_id=$request->input('productID');
    // date manupulation
    $currentDate = Carbon::now();
    $minimumExpiryDate = $currentDate->addDays(14);
    $batch_no = OpenProductBatch::where('pharmacy_id', $pharmacy_id)->where('product_id', $product_id)->where('status','open')->whereDate('exp_date', '>=', $minimumExpiryDate)->get();
    $current_stock=CurrentStock::with(['product:id,retail_max_price,coverImage'])->where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->first();
     return response()->json([
        'stock'=>$current_stock,
        'batch_no'=>$batch_no
     ]);
}
//get stock details base on batch select
public function stock_details(Request $request)
{
$pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
$openBatchID=$request->input('openBatchID');
$batch_no_details = OpenProductBatch::find($openBatchID);

return response()->json([
    'stock_details'=>null,
    'batch_no_details'=>$batch_no_details
 ]);
}
//offline sale confirm
public function off_sale(Request $request)
{
    //Validate the request
    $validator = Validator::make($request->all(), [
        "customer_name"=>'nullable',
        "grand_total"=>'required',
        "saleItems"=>'required'
    ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
        $requestData = $request->all();
        $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
        $pharmacy_user_id = Auth::guard('pharmacy')->user()->id;
        $saleItems = $request->input('saleItems');
        // date manupulation
        $currentDate = Carbon::now();
        $minimumExpiryDate = $currentDate->addDays(14);
        // stock data in sale table
        $off_sale = OffSale::create([
            "pharmacy_id"=>$pharmacy_id,
            "customer_name"=>$requestData['customer_name'],
            'grand_discount_total' => $requestData['grand_total'] // change in future.becasue look in table grand_total is mrp total which is grand_total. for time short i did it
            ]);
            // get current inserted sale id
            $off_salechild_id = $off_sale->id;
            foreach ($saleItems as  $itemData) 
            {
                // catch necessary data first
                $product_id = $itemData['product_id'];
                $max_retail_price = $itemData['max_retail_price'];
                $sale_price = $itemData['sale_price'];
                $openBatchID = $itemData['openBatchID'];
                $quantity = $itemData['quantity'];
                if($openBatchID==null)
                {
                  $remainingQty=$quantity;
                  $openBatches = OpenProductBatch::where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->whereDate('exp_date', '>=', $minimumExpiryDate)
                  ->where('status','open')->get();
                  foreach($openBatches as $batch)
                  {
                    if ($remainingQty <= 0){break;}
                    if ($remainingQty >= $batch->available){
                    // Take everything from this batch
                    $deductQty = $batch->available;// how many will be deduct
                    $remainingQty -= $batch->available;
                    $batch->available = 0;
                    $batch->status ='used';
                    }else{
                    // Take only part of this batch
                    $deductQty = $remainingQty; // how many will be deduct
                    $batch->available -= $remainingQty;
                    $remainingQty = 0;
                    }
                    // update batchees
                    $batch->save();
                    // Create OffSaleItem record with correct quantity
                    OffSaleItem::create([
                        'off_sales_id'     => $off_salechild_id,
                        'product_id'      => $product_id,
                        'max_retail_price'=> $max_retail_price,
                        'purchase_price'  => $batch->purchase_price,
                        'sale_price'      => $sale_price,
                        'batch_no'        => $batch->batch_no,
                        'quantity'        => $deductQty
                    ]);
                    // Create Stock record with correct quantity
                    Stock::create([
                        'pharmacy_id'     => $pharmacy_id,
                        'product_id'      => $product_id,
                        'off_sales_id'    => $off_salechild_id,
                        'batch_no'        => $batch->batch_no,
                        'stock_out'       => $deductQty,
                        'mfg_date'        => $batch->mfg_date,
                        'expire_date'     => $batch->exp_date
                        
                    ]);
                    // update current stock table
                     CurrentStock::where('pharmacy_id', $pharmacy_id)->where('product_id', $product_id)->decrement('in_stock',$quantity);
                  }
                }else{
                    $batch = OpenProductBatch::find($openBatchID);
                    if ($batch)
                    {
                       $batch->available -= $quantity;
                        if ($batch->available <= 0)
                        {
                            $batch->available = 0;
                            $batch->status = 'used';
                        }
                        $batch->save();
                    }
                    // off sale item record create
                    OffSaleItem::create([
                        'off_sales_id'     => $off_salechild_id,
                        'product_id'       => $product_id,
                        'max_retail_price' => $max_retail_price,
                        'purchase_price'   => $batch->purchase_price,
                        'sale_price'       => $sale_price,
                        'batch_no'         => $batch->batch_no,
                        'quantity'         => $quantity
                    ]);
                    //create record for main stock table
                    Stock::create([
                        'pharmacy_id'   => $pharmacy_id,
                        'product_id'    => $product_id,
                        'off_sales_id'  => $off_salechild_id,
                        'batch_no'      => $batch->batch_no,
                        'stock_out'     => $quantity,
                        'mfg_date'      => $batch->mfg_date,
                        'expire_date'   => $batch->exp_date
                    ]);
                    // decrement on CurrentStock table
                    CurrentStock::where('pharmacy_id', $pharmacy_id)
                        ->where('product_id', $product_id)
                        ->decrement('in_stock', $quantity);
                
              }
    }
            return response()->json([
                    'message' => "Success ! Sale generate successfully"
                ],200);
 }

}
/*
-----------------
Off-Line Sale End
------------------
*/ 

/*
------------------
Online Sale
------------------
*/ 
// get order status
public function order_count()
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $status=["confirmed","processing"];
    $active_order = Order::where('pharmacy_id',$pharmacy_id)->whereIn('status',$status)->count();
    $today_order=Order::where('pharmacy_id',$pharmacy_id)->where('orderDate',Carbon::today())->count();
    return response()->json([
        "active_order"=>$active_order,
        "today_order"=>$today_order,
        ]);
}
// search confirm order
public function searchOrder(Request $request)
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $orderNumber=$request->input('orderNumber');
    $status=['Confirmed','Processing'];
    $orders=Order::with(['orderItems.product:id,productName,cart_text,type,quantity,prescription,coverImage,company_id','orderItems.product.company','orderPrescriptions','users:id,firstName,lastName,phoneNumber'])->where('pharmacy_id',$pharmacy_id)->whereIn('status',$status)->where('orderNo','like','%'.$orderNumber.'%')->get();
    return response()->json([
        "orders"=>$orders
      ]);
} 
// get prouduct stock details
public function online_stockDetails(Request $request)
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $product_id=$request->input('productID');
    // date manupulation
    $currentDate = Carbon::now();
    $minimumExpiryDate = $currentDate->addDays(14);
    $batch_no = OpenProductBatch::where('pharmacy_id', $pharmacy_id)->where('product_id', $product_id)->where('status','open')->whereDate('exp_date', '>=', $minimumExpiryDate)->get();
    $current_stock=CurrentStock::with(['product:id,retail_max_price'])->where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->first();
     return response()->json([
        'current_stock'=>$current_stock,
        'batch_no'=>$batch_no,
     ]);
}
// get product last stock data base on batch number
public function online_openBatchStockDetails(Request $request)
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $batchID=$request->input('batchID');
    $batch_stock_details = OpenProductBatch::find($batchID);
    
    return response()->json([
        'batch_stock_details'=>$batch_stock_details
     ]);
}

// online sale generate 
public function online_sale(Request $request)
{
    //Validate the request
    $validator = Validator::make($request->all(), [
        "order_id"=>'required',
        "pickupValue"=>'required',
        "saleItems"=>"required|array"
    ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
        $pharmacyUser = Auth::guard('pharmacy')->user();
        $pharmacy_id  =$pharmacyUser->pharmacy_id;
        // get order 
        $order_id = $request->input('order_id');
        $order = Order::find($order_id);
        $pickupValue = $request->input('pickupValue');
        $saleItems = $request->input('saleItems');
        $status;
        // date manupulation
        $currentDate = Carbon::now();
        $minimumExpiryDate = (clone $currentDate)->addDays(14);
        if($pickupValue ==='self_pick')
        {
        $status = 'Delivered';
        }else if($pickupValue ==='rider_pick')
        {
        $status = 'Processing';
        }
        // stock data in sale table
        $sale = MSale::create([
                    "pharmacy_id"=>$pharmacy_id,
                    "order_id"=>$order_id,
                    "user_id"=>$order->user_id,
                    "pharmacy_seller_name" => trim($pharmacyUser->firstName . ' ' . $pharmacyUser->lastName),
                    "pharmacy_seller_email"=>$pharmacyUser->email,
                    "pharmacy_seller_phoneNumber"=>$pharmacyUser->phoneNumber,
                    "salesPrice"=>$order->offer_total_amount,
                    "comission_amount"=>$order->comission_amount,
                    "saleDate"=>$currentDate,
                    "paymentMethod"=>$order->payment_method,
                    "status"=>$status,
                ]);
        // balance update 
        $balance_update = PharmacyAccount::where('pharmacy_id', $pharmacy_id)->decrement('balance', $order->comission_amount);
        // get current inserted sale id
        $sale_id = $sale->id;
        if($order && $pickupValue==='self_pick'){
        $order->update(['status'=>'Delivered']);
        }else if($order && $pickupValue==='rider_pick'){
        $order->update(['status'=>'ReadyForPickup']);
        }
         foreach ($saleItems as  $itemData) 
         {
                // catch necessary data first
                $product_id = $itemData['product_id'];
                $max_retail_price = $itemData['max_retail_price'];
                $sale_price = $itemData['offer_price'];
                $openBatchID = $itemData['openBatchID'];
                $quantity = $itemData['quantity'];
                if($openBatchID==null)
                {
                  $remainingQty=$quantity;
                  $openBatches = OpenProductBatch::where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->whereDate('exp_date', '>=', $minimumExpiryDate)
                  ->where('status','open')->get();
                  foreach($openBatches as $batch)
                  {
                    if ($remainingQty <= 0){break;}
                    if ($remainingQty >= $batch->available){
                    // Take everything from this batch
                    $deductQty = $batch->available;// how many will be deduct
                    $remainingQty -= $batch->available;
                    $batch->available = 0;
                    $batch->status ='used';
                    }else{
                    // Take only part of this batch
                    $deductQty = $remainingQty; // how many will be deduct
                    $batch->available -= $remainingQty;
                    $remainingQty = 0;
                    }
                    // update batchees
                    $batch->save();
                    // Create OffSaleItem record with correct quantity
                    MSaleItem::create([
                        'm_sales_id'      => $sale_id,
                        'product_id'      => $product_id,
                        'max_retail_price'=> $max_retail_price,
                        'purchase_price'  => $batch->purchase_price,
                        'offer_price'     => $sale_price,
                        'batch_no'        => $batch->batch_no,
                        'quantity'        => $deductQty
                    ]);
                    // Create Stock record with correct quantity
                    Stock::create([
                        'pharmacy_id'     => $pharmacy_id,
                        'product_id'      => $product_id,
                        'm_sales_id'     => $sale_id,
                        'batch_no'        => $batch->batch_no,
                        'stock_out'       => $deductQty,
                        'mfg_date'        => $batch->mfg_date,
                        'expire_date'     => $batch->exp_date
                        
                    ]);
                    // update current stock table
                    CurrentStock::where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->decrement('in_stock',$quantity);
                  }
                }else{
                    $batch = OpenProductBatch::find($openBatchID);
                    if ($batch)
                    {
                      $batch->available -= $quantity;
                        if ($batch->available <= 0)
                        {
                            $batch->available = 0;
                            $batch->status = 'used';
                        }
                        $batch->save();
                    }
                    // off sale item record create
                     MSaleItem::create([
                        'm_sales_id'      => $sale_id,
                        'product_id'      => $product_id,
                        'max_retail_price'=> $max_retail_price,
                        'purchase_price'  => $batch->purchase_price,
                        'offer_price'     => $sale_price,
                        'batch_no'        => $batch->batch_no,
                        'quantity'        => $quantity
                    ]);
                    //create record for main stock table
                    Stock::create([
                        'pharmacy_id'     => $pharmacy_id,
                        'product_id'      => $product_id,
                        'm_sales_id'     => $sale_id,
                        'batch_no'        => $batch->batch_no,
                        'stock_out'       => $quantity,
                        'mfg_date'        => $batch->mfg_date,
                        'expire_date'     => $batch->exp_date
                        
                    ]);
                    // decrement on CurrentStock table
                    CurrentStock::where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->decrement('in_stock',$quantity);
                
              }
    }
            return response()->json([
                'message' => $sale_id,
            ],200);
    }
}
/*
------------------
Online sale End
===================
*/ 
//get all order from pharmacy
public function get_order_details()
{
        $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
        $yesterday = Carbon::yesterday()->toDateString();
        $status = ['confirmed'];
        $order = Order::with(['users','orderPrescriptions.precription','orderItems','orderItems.product:id,productName,genericName,retail_max_price,retail_min_offer_price,unit_in_pack,type,quantity,prescription,feature,coverImage'])
        ->where('id','like','%'.$id.'%')
        ->where('pharmacy_id',$pharmacy_id)
        ->whereIn('status',$status)
        ->get();
        if(count($order) == 0)
        {
          return "Not Found ! Make sure,it's an active,self-picked or rider-picked order";
          exit();
        }
       //decode json formate data
       $data = json_decode($order,true);
       // access order items
       $orderItems = $data[0]['order_items'];
       //empty array of order id's
       $orderIds =[];
       foreach ($orderItems as $item) {
        $productId = $item['product_id'];
        array_push($orderIds,$productId);
       }

        $open_batch_product = OpenProductBatch::where('pharmacy_id',$pharmacy_id)
                              ->whereIn('product_id',$orderIds)
                              ->where('status','open')
                              ->whereDate('exp_date', '>=', $yesterday)
                              ->get();

        $latestRecords = DB::table('stock_products AS sp')
                        ->select('sp.*')
                        ->join(DB::raw('(SELECT product_id, MAX(created_at) AS max_created_at FROM stock_products GROUP BY product_id) AS latest'), function ($join) {$join->on('sp.product_id', '=', 'latest.product_id')->on('sp.created_at', '=', 'latest.max_created_at');
                        })->whereIn('sp.product_id', $orderIds)->get();


        return response()->json([
            "order_details"=>$order,
            'product_open_batch'=>$open_batch_product,
            'latest_product_stock_details'=>$latestRecords
          ]);
    }
    
/*
------------------------
Common-off-Online sale
-------------------------
*/ 
public function get_new_batch_no(Request $request)
{
      $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
      // date manupulation
      $currentDate = Carbon::now();
      $minimumExpiryDate = $currentDate->addDays(14);
    //   $batch_no=$request->input('batch_no');
      $product_id=$request->input('product_id');
      // open batches
      $opened_stock_ids = OpenProductBatch::where('pharmacy_id', $pharmacy_id)
        ->where('product_id', $product_id)
        ->pluck('product_stock_id')
        ->toArray();
  
      $batch_no = StockProduct::select('id','product_id','pharmacy_id','expire_date','batch_no','mfg_date','qty','created_at','updated_at')
                ->where('pharmacy_id',$pharmacy_id)
                ->where('product_id',$product_id)
                ->whereNotIn('id',$opened_stock_ids)
                ->whereDate('expire_date', '>=', $minimumExpiryDate)
                ->orderBy('expire_date', 'asc')
                ->get();
      return $batch_no;
}

    //open batch number
public function open_batch(Request $request)
{
      //Validate the request
      $validator = Validator::make($request->all(), [
        "id"=>'required',
    ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
        $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
        $id = $request->input('id');
        $status = "open";
        // get offer price from stock product table
        $batch_no_details= StockProduct::find($id);
        $open_batch= OpenProductBatch::create([
            'pharmacy_id'=>$pharmacy_id,
            'product_stock_id'=>$id,
            'product_id'=>$batch_no_details->product_id,
            'batch_no'=>$batch_no_details->batch_no,
            'status'=>$status,
            'available'=>$batch_no_details->qty,
            'qty_stock'=>$batch_no_details->qty,
            'purchase_price'=>$batch_no_details->purchase_price,
            'offer_price'=>$batch_no_details->offer_price,
            'mfg_date'=>$batch_no_details->mfg_date,
            'exp_date'=>$batch_no_details->expire_date,
        ]);
    }
    return response()->json(['data'=>$open_batch]);
}


/*
------------------------------
common off-online sale end
------------------------------
*/ 


/*
----------------
Sale Report
----------------
*/ 
public function sale_report(Request $request)
{
    $srcStartDate = $request->input('srcStartDate');
    $srcEndDate = $request->input('srcEndDate');
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $status=['Delivered'];
    $busines_percentage = PharmacyBusinessSetup::select('comissionPercent')->where('pharmacy_id',$pharmacy_id)->first();
    $sale_report = MSale::with('user:id,firstName,lastName,phoneNumber,division,district,upazilla','saleItems.product:id,productName,type,quantity,coverImage')->where('pharmacy_id',$pharmacy_id)->whereBetween('saleDate', [$srcStartDate, $srcEndDate])->whereIn('status',$status)->get();
    return response()->json([
    'busines_percentage' => $busines_percentage,
    'sale_report' => $sale_report,
    ]);
}
public function search_sale_report(Request $request)
{
    $order_id = $request->input('order_id');
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $sale_report = MSale::with('user:id,firstName,lastName,phoneNumber,division,district,upazilla','saleItems.product:id,productName,type,quantity,coverImage')->where('pharmacy_id',$pharmacy_id)->where('order_id',$order_id)->first();
    return response()->json([
    'sale_report' => $sale_report
    ]);
}

public function off_sale_product_details($product_id)
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $available = CurrentStock::where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->get();
    $stock_product=StockProduct::where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->latest()->first();
    return response()->json([
        'available'=>$available,
        'stock_product'=>$stock_product,
    ]);
}

/*
--------------------------------------
Current sale - Off and Online both
--------------------------------------
*/ 
public function currentSale()
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $currentOfflineSales = OffSale::with('saleItems.product:id,productName,type,quantity,coverImage')->where('pharmacy_id', $pharmacy_id)
                           ->orderBy('created_at', 'desc')->take(5)->get();
    $currentOnlineSales  = MSale::with('saleItems.product:id,productName,type,quantity,coverImage')->where('pharmacy_id', $pharmacy_id)
                           ->orderBy('created_at', 'desc')->take(5)->get();

    // Merge collections into one array
    $allSales = $currentOfflineSales->concat($currentOnlineSales)->toArray();

    // Format each sale
    $formattedSales = array_map(function ($sale) {
        $isOnline = isset($sale['order_id']); // online sale if order_id exists
        $isOffline = !$isOnline;

        return [
            'method' => $isOffline ? 'offline' : 'online',
            'bill' => $isOffline ? ($sale['grand_discount_total'] ?? null) : ($sale['salesPrice'] ?? null),
            'commission' => $isOnline ? ($sale['comission_amount'] ?? null) : null,
            'created_at' => $sale['created_at'] ?? null,
            'totalItem' => count($sale['sale_items'] ?? []),  // use 'saleItems' key from relation
            'sale_items' => array_map(function ($item) use ($isOffline, $isOnline) {
                return [
                    'Rate' => $isOffline ? ($item['sale_price'] ?? null) : ($item['offer_price'] ?? null),
                    'product_id' => $item['product_id'] ?? null,
                    'batch_no' => $item['batch_no'] ?? null,
                    'max_retail_price' => $item['max_retail_price'] ?? null,
                    'purchase_price' => $item['purchase_price'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'product' => $item['product'] ?? null
                ];
            }, $sale['sale_items'] ?? [])
        ];
    }, $allSales);

    // Sort by created_at descending (latest first)
    usort($formattedSales, function ($a, $b) {
        return strtotime($b['created_at']) <=> strtotime($a['created_at']);
    });
    // Balance of a pharmacy
    $balance=PharmacyAccount::where('pharmacy_id', $pharmacy_id)->first();
    return response()->json([
        'formattedSales' => $formattedSales,
        'balance'=>$balance
    ], 200);
}


}


