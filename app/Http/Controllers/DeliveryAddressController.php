<?php
namespace App\Http\Controllers;
use Auth;
use Validator;
use Illuminate\Http\Request;
use App\Models\PickupAddress;
use App\Models\DeliveryAddress;
use App\Models\ActiveDeliveryAddress;


class DeliveryAddressController extends Controller
{

    public function index()
    {
        $user_id=Auth::user()->id;
        $addresses = DeliveryAddress::where('user_id',$user_id)->get();
        $active_delivery_address = ActiveDeliveryAddress::with('address')->where('user_id',$user_id)->first();
        return response()->json([
            'addresses'=>$addresses,
            'active_delivery_address'=>$active_delivery_address,
        ]);

    }

public function store(Request $request)
    {
      //Validate the request
    $validator = Validator::make($request->all(), [
        'fullName'=>'required',
        'contactNumber'=>'required',
        'division'=>'required',
        'district'=>'required',
        'upazilla'=>'required',
        'pickupPoint'=>'required',
        'type'=>'required',
      ]);
      // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
        $user_id=Auth::user()->id;
        $addresses = DeliveryAddress::where('user_id',$user_id)->get();
        if ($addresses->count() >= 3) {
            return response()->json(['err' => 'User cannot add more than 3 addresses.'], 400);
        }
        DeliveryAddress::create([
        'fullName'=>$request->input('fullName'),
        'contactNumber'=>$request->input('contactNumber'),
        'division'=>$request->input('division'),
        'district'=>$request->input('district'),
        'upazilla'=>$request->input('upazilla'),
        'pickupPoint'=>$request->input('pickupPoint'),
        'extraInfo'=>$request->input('extraInfo'),
        'type'=>$request->input('type'),
        'user_id'=>$user_id
        ]);
      return response()->json(['success'=>'Data Submited Successfully']);
     }
    }


    public function show($id)
    {
       //here id is address table id
       $address=DeliveryAddress::find($id);
       return $address;
    }


    public function update(Request $request, $id)
    {
        //Validate the request
    $validator = Validator::make($request->all(), [
        'fullName'=>'required',
        'contactNumber'=>'required',
        'division'=>'required',
        'district'=>'required',
        'upazilla'=>'required',
        'pickupPoint'=>'required',
        'type'=>'required'

      ]);
    // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
        $user_id=Auth::user()->id;
        DeliveryAddress::where('id',$id)->update([
        'fullName'=>$request->input('fullName'),
        'contactNumber'=>$request->input('contactNumber'),
        'division'=>$request->input('division'),
        'district'=>$request->input('district'),
        'upazilla'=>$request->input('upazilla'),
        'pickupPoint'=>$request->input('pickupPoint'),
        'extraInfo'=>$request->input('extraInfo'),
        'type'=>$request->input('type'),
        'user_id'=>$user_id
        ]);
      return response()->json(['success'=>'Data Updated Successfully']);
     }
    }

    public function destroy($id)
    {
        //here id is address table id
       $address=DeliveryAddress::find($id);
       $address->delete();
       return response()->json(['message'=>'Data Deleted Successfully']);
    }

    /*
    --------------------------
     Active delivery address
    ---------------------------
    */
    public function set_active_delivery_address(Request $request)
    {
        $user_id=Auth::user()->id;
        $active_delivery_address = ActiveDeliveryAddress::updateOrCreate(
                ['user_id'=>$user_id],
                ['user_id'=>$user_id ,'delivery_address_id'=>$request->input('delivery_address_id')]
        );
       $get_active_delivery_address = ActiveDeliveryAddress::where('user_id',$user_id)->get();
       return response()->json([
        'data'=>$get_active_delivery_address,
        'success'=>'Set Active Delivery Address'
       ]);
    }
    public function get_active_delivery_address_details()
    {
        $user_id=Auth::user()->id;
        $active_delivery_address=ActiveDeliveryAddress::where('user_id',$user_id)->first();
        $deliver_address_id = $active_delivery_address->delivery_address_id;
        $address=DeliveryAddress::find($deliver_address_id);
        return $address;
    }
    /*
    --------------------------
    Set Pickup address
    ---------------------------
    */
    public function set_pickup_address(Request $request)
    {
        $user_id=Auth::user()->id;
         //Validate the request
    $validator = Validator::make($request->all(), [
            'division'=>'required',
            'district'=>'required',
            'upazilla'=>'required',
            'area'=>'required'
      ]);
      // validate error message response
    if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
    }else{
        $pickup_address = PickupAddress::updateOrCreate(
            ['user_id'=>$user_id],
            [
                'user_id'=>$user_id ,
                'division'=>$request->input('division'),
                'district'=>$request->input('district'),
                'upazilla'=>$request->input('upazilla'),
                'area'=>$request->input('area')
            ]
         );
      return response()->json(['success'=>'Pickup address save']);
     }
   }
    public function get_pickup_address()
    {
        $user_id=Auth::user()->id;
        $pickup_address=PickupAddress::where('user_id',$user_id)->first();
        return $pickup_address;
    }
}
