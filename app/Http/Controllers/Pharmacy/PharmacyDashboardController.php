<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CurrentStock;
use App\Models\ExpireProduct;
use App\Models\Order;
use App\Models\StockProduct;
use App\Models\Stock;
use App\Models\MSaleItem;
use App\Models\OffSaleItem;
use Carbon\Carbon;
use Auth;

class PharmacyDashboardController extends Controller
{
  /*
  ======================
  Highlite section eng
  ======================
  */
  public function stockHighlight()
  {
    $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
    $counts = CurrentStock::selectRaw('
    SUM(CASE WHEN in_stock >= 1 THEN 1 ELSE 0 END) as in_stock_count,
    SUM(CASE WHEN in_stock = 0 THEN 1 ELSE 0 END) as out_of_stock_count')
    ->where('pharmacy_id', $pharmacy_id)
    ->first(); 
    $expire = ExpireProduct::where('pharmacy_id', $pharmacy_id)->count();
    $in_stock = $counts->in_stock_count;
    $out_of_stock = $counts->out_of_stock_count;
    $total = $in_stock + $out_of_stock;
    return response()->json([
        'total'=>$total,
        'in_stock'=>$in_stock,
        'out_of_stock'=>$out_of_stock,
        'expire'=>$expire
      ]);

  }
 /*
 ======================
 Highlite section eng
 ======================
 */
// get today order
public function getTodayOrder()
{
  $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
  // Get today's date
  $today = Carbon::today();
  $orders = Order::with(['users'])
  ->withCount('orderItems')
  ->where('pharmacy_id', $pharmacy_id)
  ->whereDate('orderDate', $today)
  ->orderBy('id', 'DESC')
  ->get();
 return $orders;
}
// get active order
public function getActiveOrder()
{
  $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
  $status=['Confirmed','Processing'];
  // Get today's date
  $today = Carbon::today();
  $orders = Order::with(['users'])
  ->withCount('orderItems')
  ->where('pharmacy_id', $pharmacy_id)
  ->whereIn('status', $status)
  ->orderBy('id', 'DESC')
  ->get();
 return $orders;
}
// get top selling product
public function getTopSale()
{
  $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
  $currentMonth = Carbon::now()->month;
  $currentYear = Carbon::now()->year;
  // Assuming Stock model represents the stocks table
  $topSellingProducts =Stock::with(['product:id,productName,quantity,type,coverImage'])
  ->select('product_id')
  ->selectRaw('SUM(stock_out) as total_stock_out')
  ->where('pharmacy_id', $pharmacy_id)
  ->whereMonth('created_at', $currentMonth)  // Filter by current month
  ->whereYear('created_at', $currentYear)
  ->groupBy('product_id')
  ->orderBy('total_stock_out', 'desc')
  ->paginate(10);
  return $topSellingProducts;

}

public function getExpireProduct()
{
  $pharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
  $expireStockProduct = StockProduct::with('product:id,productName,type')->where('pharmacy_id',$pharmacy_id)->get();
  return response()->json([
    'expire'=>$expireStockProduct
  ]);
}

}
