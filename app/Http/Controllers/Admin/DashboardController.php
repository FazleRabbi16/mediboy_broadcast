<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Rider;
use App\Models\ActiveShift;
use App\Models\RiderDelivery;
use App\Models\RiderWallet;
use App\Models\RiderShift;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PharmacyBusinessSetup;

class DashboardController extends Controller
{
  // admin dashboard highlite
   public function all_highlites()
   {
    $userCounts = User::count();
    $pharmacyCounts = PharmacyBusinessSetup::where('status','Active')->count();
    $orderCounts = Order::whereMonth('orderDate', Carbon::now()->month)
                        ->whereYear('orderDate', Carbon::now()->year)->count();
    $riderCounts = Rider::count();
    return response()->json([
        "userCounts"=>$userCounts,
        "pharmacyCounts"=>$pharmacyCounts,
        "orderCounts"=>$orderCounts,
        "riderCounts"=>$riderCounts,
        ]);
   }
   // rider active shift
   public function riderActiveShiftToday()
   {
    $activeRiderShift = ActiveShift::whereDate('activeDateTime', Carbon::today())
    ->where('givenEndTime', '>', Carbon::now())
    ->get();
    return response()->json([
      "activeRiderShift"=>$activeRiderShift,
      ]);

   }
   // active order
   public function activeOrder()
   {
    $status=['Confirmed','Processing'];
    $activeOrder=Order::with(['deliveryToAddress'])->whereIn('status',$status)->orderBy('id','DESC')->get();
    return response()->json([
    'activeOrder'=>$activeOrder,
    ]);
   }
   // active delivery
   public function activeDelivery()
   {
    $status=['Processing','Pickup'];
    $activeDelivery=RiderDelivery::whereIn('status',$status)->get();
    return response()->json([
    'activeDelivery'=>$activeDelivery,
    ]);
   }
   // rider negative wallet value
   public function riderNegWallet()
   {
    $riderNegWallet = RiderWallet::with('rider')
    ->where('balance', '<', 0) // Filter for negative balances
    ->orderBy('balance', 'ASC') // Order by balance in descending order
    ->get();
    return response()->json([
    'riderNegWallet'=>$riderNegWallet,
    ]);
   }
   // today open shift of riders
   public function riderOpenShift()
   {
    $currentDate = Carbon::now()->format('Y-m-d');
    $riderOpenShift = RiderShift::with('rider')->where(function ($query) use ($currentDate) {
      $query->where('startDate', $currentDate)
            ->orWhere('endDate', $currentDate)
            ->orWhere(function ($subQuery) use ($currentDate) {
                $subQuery->where('startDate', '<=', $currentDate)
                         ->where('endDate', '>=', $currentDate);
            });
    })
  ->get();
    return response()->json([
    'shifts' => $riderOpenShift
    ]);
   }

   public function get_today_orders(){
        $todayOrders = Order::with(['pharmacy:id,pharmacyName,googleLink','deliveryAddress','orderPrescriptions.precription','orderItems', 'orderItems.product:id,productName,genericName,retail_max_price,retail_min_offer_price,unit_in_pack,type,quantity,prescription,feature','users'])
        ->whereDate('created_at',Carbon::today())
        ->orderBy('created_at', 'desc')
        ->paginate(100);
      return $todayOrders;
    }
}
