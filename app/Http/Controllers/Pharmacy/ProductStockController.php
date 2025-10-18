<?php

namespace App\Http\Controllers\Pharmacy;

use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Category;
use App\Models\CurrentStock;
use App\Models\StockProduct;
use App\Models\CurrentActivePrice;
use App\Models\OpenProductBatch;
use App\Models\ExpireProduct;
use App\Models\CheckExpireProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class ProductStockController extends Controller
{
 /*
  -Get minimum offer price
  -Get maximum retail price
 */
public function get_product_details(Request $request)
{
    $product_id=$request->input('product_id');
    $product_details = Product::select('id','productName','type','quantity','genericName','coverImage','retail_max_price','retail_min_offer_price','unit_in_pack','percentage_off','prescription','feature','category_id','company_id')->where('id',$product_id)->first();
    return response()->json([
        'product'=>$product_details
    ]);
}
//pharmacy stock own shop product
public function stock_product(Request $request)
{
        //Validate the request
    $validator = Validator::make($request->all(), [
        'product_id'=>'required',
        'stock_mrp'=>'required',
        'purchase_price'=>'required',
        'discount_price'=>'required',
        'peak_hour_price'=>'required',
        'offer_price'=>'required',
        'perc_off'=>'required',
        'batch_no'=>'required',
        'mfg_date'=>'required',
        'expire_date'=>'required',
        'qty'=>'required',
    ]);
    // validate error message response
   if ($validator->fails()) {
    return response()->json(['errors'=>$validator->errors()]);
    }else{
     $pharmacyUserDetails = Auth::guard('pharmacy')->user();
     $pharmacy_id = $pharmacyUserDetails->pharmacy_id;
     $product_id=$request->input('product_id');
     $stock_mrp=$request->input('stock_mrp');
     $purchase_price=$request->input('purchase_price');
     $discount_price=$request->input('discount_price');
     $peak_hour_price=$request->input('peak_hour_price');
     $offer_price=$request->input('offer_price');
     $perc_off=$request->input('perc_off');
     $mfg_date=$request->input('mfg_date');
     $batch_no=$request->input('batch_no');
     $qty=$request->input('qty');
     $shelf=$request->input('shelf');
     $expire_date=$request->input('expire_date');
     // create stock product table(child) data
     $stockProductChild= StockProduct::create([
        'pharmacy_id'=>$pharmacy_id,
        'stock_mrp'=>$stock_mrp,
        'product_id'=>$product_id,
        'purchase_price'=>$purchase_price,
        'offer_price'=>$offer_price,
        'perc_off'=>$perc_off,
        'batch_no'=>$batch_no,
        'mfg_date'=>$mfg_date,
        'expire_date'=>$expire_date,
        'qty'=>$qty,
        'shelf'=>$shelf,
    ]);
    $stock_product_id = $stockProductChild->id;
    //add product in stock master table
    $stockProduct = Stock::create([
        'pharmacy_id'=>$pharmacy_id,
        'product_id'=>$product_id,
        'stock_product_id'=>$stock_product_id,
        'stock_in'=>$qty,
        'batch_no'=>$batch_no,
        'mfg_date'=>$mfg_date,
        'expire_date'=>$expire_date,
    ]);
    // update or create current stock
    $current_stock=CurrentStock::where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->first();
    if($current_stock){
       $update_qty = $current_stock->in_stock+$qty;
       $current_stock->update(['in_stock'=>$update_qty,'discount_price'=>$discount_price,'peak_hour_price'=>$peak_hour_price,'mediboy_offer_price'=>$offer_price]);
    }else{
        $new_current_stock = CurrentStock::create([
            'pharmacy_id' => $pharmacy_id,
            'product_id' => $product_id,
            'in_stock' => $qty,
            'sale_price' => $discount_price,
            'discount_price' => $discount_price,
            'peak_hour_price' => $peak_hour_price,
            'mediboy_offer_price' => $offer_price
        ]);
    
        if (!$new_current_stock) {
            return response()->json(["error" => "Failed to create new current stock entry"], 500);
        }
    }
    return response()->json(["msg"=>"Product stock successfully"]);
     }
}
public function get_current_stock()
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $in_stock = CurrentStock::where('in_stock', '>=', 1)->where('pharmacy_id',$pharmacy_id)->count();
    $out_of_stock = CurrentStock::where('in_stock', '=', 0)->where('pharmacy_id',$pharmacy_id)->count();
    $current_stock = CurrentStock::with(['product:id,productName,quantity,cart_qty_inc,cart_text,unit_in_pack,type,retail_max_price,coverImage,company_id','product.company'])
    ->where('pharmacy_id', $pharmacy_id)
    ->orderBy('in_stock') // Sort by in_stock in ascending order
    ->paginate(5);
    return response()->json([
      'in_stock'=>$in_stock,
      'out_of_stock'=>$out_of_stock,
      'currentStock'=>$current_stock
    ]);
}

public function search_current_stock(Request $request)
{
  $id = $request->input('id');
  $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
  $current_stock = CurrentStock::with(['product:id,productName,quantity,cart_qty_inc,cart_text,unit_in_pack,type,retail_max_price,coverImage,company_id','product.company'])
    ->where('pharmacy_id', $pharmacy_id)
    ->where('product_id', $id)
    ->first();
  return response()->json([
    'data'=>$current_stock
  ]);
}
public function get_today_stock()
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $today_stock = StockProduct::with('product:id,productName,coverImage,quantity,retail_max_price')
    ->where('pharmacy_id', $pharmacy_id)
    ->whereDate('created_at', Carbon::today())
    ->orderBy('id', 'desc') // Order by id in descending order
    ->get();
    return response()->json([
    'todayStock' => $today_stock
    ]);
}
// update sale price
public function updateSalePrice(Request $request)
{
//Validate the request
$validator = Validator::make($request->all(), [
    'id'=>'required',
    'discount_price'=>'required',
    'peak_hour_price'=>'required',
]);
// validate error message response
if ($validator->fails()) {
return response()->json(['errors'=>$validator->errors()]);
}else{
$pharmacyUserDetails = Auth::guard('pharmacy')->user();
$pharmacy_id = $pharmacyUserDetails->pharmacy_id;
$id=$request->input('id');
$discount_price=$request->input('discount_price');
$peak_hour_price=$request->input('peak_hour_price');
$current_stock=CurrentStock::find($id);
$current_stock->update(['discount_price'=>$discount_price,'peak_hour_price'=>$peak_hour_price]);
if($current_stock){
    return response()->json([
      'msg'=>"Price update successfully"
    ]);
}else{
    return response()->json([
      'msg'=>"Error ! Something error,price not updated"
    ]);
}
   
}

}
public function checkExpireProduct()
{
    try {
        $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
        $checkEndDate = Carbon::now()->toDateString();
        $expireDateScale = Carbon::now()->addDays(14)->toDateString();
        
        // Determine the start date for checking expiry
        $checkExpireTillDate = CheckExpireProduct::select('checkDateTill')
            ->where('pharmacy_id', $pharmacy_id)
            ->first();
        
        if ($checkExpireTillDate !== null) {
            $checkStartDate = $checkExpireTillDate->checkDateTill;
        } else {
            $date = StockProduct::select('created_at')->where('pharmacy_id', $pharmacy_id)->first();
            $checkStartDate = $date ? Carbon::parse($date->created_at)->toDateString() : $checkEndDate;
        }
        
        // Fetch the expire products data
        $expireProducts = Stock::select('product_id','batch_no','expire_date')
            ->selectRaw('COALESCE(SUM(stock_in), 0) - COALESCE(SUM(stock_out), 0) AS quantity')
            ->where('pharmacy_id', $pharmacy_id)
            ->whereDate('created_at', '>', $checkStartDate)
            ->where('expire_date', '<=', $expireDateScale)
            ->groupBy('product_id', 'batch_no','expire_date')
            ->get();
        
        foreach ($expireProducts as $data) {
            // Attempt to create a new record in expire_products table
            ExpireProduct::create([
                'pharmacy_id' => $pharmacy_id,
                'product_id'  => $data->product_id,
                'batch_no'    => $data->batch_no,
                'quantity'    => $data->quantity,
                'expire_date' => $data->expire_date,
            ]);
            // Update the status of the batch_no in OpenProductBatch model
            OpenProductBatch::where('batch_no', $data->batch_no)
            ->update(['status' => 'expired']);
            // Update current stock in_stock
            CurrentStock::where('pharmacy_id', $pharmacy_id)
            ->where('product_id', $data->product_id)
            ->decrement('in_stock', $data->quantity); 
        }
        
        // Update or create the checkExpireProduct record
        CheckExpireProduct::updateOrCreate(
            ['pharmacy_id' => $pharmacy_id],
            ['checkDateTill' => $checkEndDate]
        );
        
        return response()->json([
            'msg'=>'Expire product check successfully',
            'expireProduct'=>$expireProducts,
            'startDate'=>$checkStartDate,
            'endDate'=>$checkEndDate,
        ]);
        
    } catch (\Exception $e) {
        // Log the exception or return a detailed error message
        return response()->json([
            'msg' => 'error',
            'error' => $e->getMessage()
        ]);
    }
}
public function get_single_product_stock_value(Request $request)
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $product_id =  $request->input('productID');
    $stock = CurrentStock::where('pharmacy_id',$pharmacy_id)->where('product_id',$product_id)->first(); 
    return response()->json([
        'stock'=>$stock
        ]);
}
public function expire()
{
  $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
  $expire_product = ExpireProduct::with(['product:id,productName,coverImage,quantity,type,company_id','product.company'])->where('pharmacy_id',$pharmacy_id)->get();
  $expireCheckData = CheckExpireProduct::where('pharmacy_id',$pharmacy_id)->first();
  return response()->json([
    'expire_product'=>$expire_product,
    'expireCheckData'=>$expireCheckData
  ]);
}
public function remove_expire_product($id)
{
  $expire_product = ExpireProduct::find($id);
  $expire_product->delete();
  return response()->json([
    'msg' =>`Expire product remove successfully`
    ]);
}
public function stock_report(Request $request)
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $start_date=$request->input('start_date');
    $end_date=$request->input('end_date');
    $stock_product = StockProduct::with(['product:id,productName,coverImage,quantity,type,company_id','product.company'])
        ->where('pharmacy_id', $pharmacy_id)
        ->whereBetween('created_at', [$start_date, $end_date])
        ->orderBy('id', 'desc')
        ->get();
    return $stock_product;
}
//change price type like discount to peak-hour and vise versa
public function changePriceType(Request $request)
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $price_type = $request->input('price_type');

    if ($price_type === 'discount') {
        // Update sale_price = discount_price
        CurrentStock::where('pharmacy_id', $pharmacy_id)
            ->update([
                'sale_price' => \DB::raw('discount_price')
            ]);
    } elseif ($price_type === 'peak') {
        // Update sale_price = peak_hour_price
        CurrentStock::where('pharmacy_id', $pharmacy_id)
            ->update([
                'sale_price' => \DB::raw('peak_hour_price')
            ]);
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid price type'
        ], 400);
    }
     //  Update or create CurrentActivePrice
    CurrentActivePrice::updateOrCreate(
        ['pharmacy_id' => $pharmacy_id], // Check condition
        ['select_price' => $price_type]  // Value to update or insert
    );

    return response()->json([
        'message' => 'Sale prices updated successfully'
    ], 200);
}
//get current price type 
public function getCurrentPriceType()
{
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;

    $currentSalePriceType = CurrentActivePrice::where('pharmacy_id', $pharmacy_id)->first();

    // যদি record না থাকে, ডিফল্ট value
    $priceType = $currentSalePriceType ? $currentSalePriceType->select_price : 'discount';

    return response()->json([
        'currentSalePriceType' => $priceType
    ], 200);
}

}
