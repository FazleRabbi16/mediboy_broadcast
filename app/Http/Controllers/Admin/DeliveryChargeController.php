<?php

namespace App\Http\Controllers\Admin;

use Validator;
use Illuminate\Http\Request;
use App\Models\DeliveryCharge;
use App\Http\Controllers\Controller;
use App\Models\DefaultOrFreeDelivery;

class DeliveryChargeController extends Controller
{
    public function get_user_delivery_charge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=>'required',
            'destination'=>'required',
            'type'=>'required' // order type home,self
        ]);
    
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
    
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $destination = $request->input('destination'); 
        $type = $request->input('type');
    
        if ($destination === 'In-area' && $type === 'home_delivery') {
            $deliveryCharge = DeliveryCharge::where('division', $division)
                ->where('district', $district)
                ->where('upazilla', $upazilla)
                ->first();
            
            if ($deliveryCharge === null) {
                $defaultCharge = DefaultOrFreeDelivery::select('default_charge_in_area')->first();
                $deliveryChargeValue = $defaultCharge ? $defaultCharge->default_charge_in_area : 0.00;
            } else {
                $deliveryChargeValue = $deliveryCharge->delivery_charge;
            }
        } else if ($destination === 'All-bd' && $type === 'home_delivery') {
            $defaultCharge = DefaultOrFreeDelivery::select('default_charge_all_bd')->first();
            $deliveryChargeValue = $defaultCharge ? $defaultCharge->default_charge_all_bd : 0.00;
        } else {
            $deliveryChargeValue = 0.00;
        }
    
        return response()->json([
            'deliveryCharge' => $deliveryChargeValue
        ]);
    }
    
    public function index()
    {
        $deliveryCharge = DeliveryCharge::orderBy('division', 'asc')->get();
       return response()->json([
        'deliveryCharge'=>$deliveryCharge,
      ]);
    }
  
   public function get_free_delivery_admin(){
     $default_charge_in_area = DefaultOrFreeDelivery::all();
     return $default_charge_in_area;
   }
    public function store(Request $request)
    {
        //store category
        $validator = Validator::make($request->all(), [
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'delivery_charge'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            //Get all text value
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $delivery_charge = $request->input('delivery_charge');
        $deliveryCharge=DeliveryCharge::create([
            'division'=>$division,
            'district'=>$district,
            'upazilla'=>$upazilla,
            'delivery_charge'=>$delivery_charge,
        ]);
        }
        return response()->json(['success'=>'Data Submited Successfully']);
    }


    public function show($id)
    {
        //find single category
        $deliveryCharge = DeliveryCharge::find($id);
        return $deliveryCharge;
    }


    public function update(Request $request, $id)
    {
        //store category
        $validator = Validator::make($request->all(), [
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'delivery_charge'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            //Get all text value
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $delivery_charge = $request->input('delivery_charge');
        $deliveryCharge=DeliveryCharge::where('id',$id)->update([
            'division'=>$division,
            'district'=>$district,
            'upazilla'=>$upazilla,
            'delivery_charge'=>$delivery_charge,
        ]);
        }
        return response()->json(['success'=>'Data Updated Successfully']);
    }


    public function destroy($id)
    {
        $deliveryCharge = DeliveryCharge::find($id);
        $deliveryCharge->delete();
        return response()->json(['success'=>'Data Deleted Successfully']);
    }
    public function free_delivery(Request $request)
    {

      //store category
      $validator = Validator::make($request->all(), [
        'default_charge_in_area'=> 'required',
        'default_charge_all_bd'=> 'required',
    ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
        //Get all text value
    $default_charge_in_area = $request->input('default_charge_in_area');
    $default_charge_all_bd = $request->input('default_charge_all_bd');
    $firstRow = DefaultOrFreeDelivery::first();
    if(empty($firstRow))
       {
        $freedelivery = DefaultOrFreeDelivery::create([
            'default_charge_in_area' =>$default_charge_in_area ,
             'default_charge_all_bd' =>$default_charge_all_bd
             ]);
       }else{
        $freedelivery = DefaultOrFreeDelivery::where('id',$firstRow->id)->update([
            'default_charge_in_area' =>$default_charge_in_area ,
             'default_charge_all_bd' =>$default_charge_all_bd
             ]);
       }
    }
      return response()->json(['success'=>'Data Submitted Successfully']);
    }

    // get free delivery up buy for user's
    public function get_free_delivery()
    {
      $free_delivery_charge_up_buy = DefaultOrFreeDelivery::select('default_charge_all_bd')->first();
      return $free_delivery_charge_up_buy;
    }

}
