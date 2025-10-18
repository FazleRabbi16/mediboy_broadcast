<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RiderShift;
use App\Models\ActiveShift;
use App\Models\RiderShiftPlaceWithCommission;
use App\Models\Order;
use App\Models\RiderDelivery;
use Carbon\Carbon;
use Validator;
use Auth;

class RiderShiftController extends Controller
{
    //Add shift 
    public function addShift(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'startDate'=> 'required',
            'endDate'=> 'required',
            'startTime'=> 'required',
            'endTime'=> 'required'
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $division = $request->input('division');
            $district = $request->input('district');
            $upazilla = $request->input('upazilla');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            $startTime = $request->input('startTime');
            $endTime = $request->input('endTime');
            $status='Active';
            $rider=Auth::guard('rider')->user();
            $rider_id = $rider->id;
            // Convert endDate to Carbon instance
            $currentDateTime=Carbon::now();
            // Format it to only include the date part
            $currentDate = $currentDateTime->format('Y-m-d');
            // Calculate the date 7 days from the current date
            $datePlus7DaysTime = Carbon::parse($currentDate)->addDays(7);
            // Optionally, format it to match the endDate format
            $datePlus7Days = $datePlus7DaysTime->format('Y-m-d');
            //shift validation date
            if ($startDate > $currentDate || $endDate > $datePlus7Days ) {
            return response()->json([
            'msg' => 'You can add shift from today to next 7 days'
            ]);
            }
            // shift validation time.Only user can set time of a particular date of time . like 00.00 to 23.59 min. not like that 22.00 - 2.30(the next date). so check it
            // Convert startTime and endTime to Carbon instances
            $startTime = Carbon::createFromFormat('H:i', $startTime);
            $endTime = Carbon::createFromFormat('H:i', $endTime); 
            if ($startTime > $endTime) {
            return response()->json([
            'msg' => 'Invalid time range. Time must be within the same day (00:00 to 23:59).',
            ]);
            }
            $riderShift= RiderShift::create([
                'rider_id' =>$rider_id,
                'division' =>$division,
                'district' => $district,
                'upazilla' => $upazilla,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'status' => $status
            ]);
            return response()->json([
                'msg'=>"Shift add successfully",
            ]);
        }
    }
    // Auth user get own shift
    public function getUserShift()
    {
        $rider=Auth::guard('rider')->user();
        $rider_id = $rider->id;
        $shift = RiderShift::where('rider_id',$rider_id)->where('status','Active')->get();
        return response()->json([
        'shift'=>$shift
        ]);
    }
    // Auth user remove own shift
    public function removeShift(Request $request)
    {
        $shift_id=$request->input('shift_id');
        $status='Canceled';
        $rmvShift=RiderShift::where('id',$shift_id)->update(['status'=>$status]);
        if($rmvShift)
        {
        return response()->json([
        'msg'=>'Shift cancel successfully'
        ]);
        }else{
        return response()->json([
        'err'=>'Shift does not cancel'
        ]); 
        }
    }
    //Auth user active shift
    public function activeShift(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'givenStartTime'=> 'required',
            'givenEndTime'=> 'required',
            'vehicleType'=> 'required',
            'rider_shift_id'=> 'required',
            'bodyTemp'=> 'nullable|integer',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        $rider=Auth::guard('rider')->user();
        $rider_id = $rider->id;
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $vehicleType = $request->input('vehicleType');
        $givenStartTime = $request->input('givenStartTime');
        $givenEndTime = $request->input('givenEndTime');
        $bodyTemp = $request->input('bodyTemp');
        $rider_shift_id = $request->input('rider_shift_id');
        $is_online=1;
        $on_delivery=0;
        // Convert endDate to Carbon instance
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        // Format the current date to 'Y-m-d'
        $currentDate = Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i:s');
        $isExistInActiveShift = ActiveShift::where('rider_id',$rider_id)->whereDate('activeDateTime','=',$currentDate)->whereTime('givenEndTime','>',$currentTime)->count();
        if($isExistInActiveShift > 0){
        return response()->json([
        'msg'=>'Opps ! you already active a shift.',
        'data'=>$isExistInActiveShift
        ]);
        }else if($currentTime > $givenEndTime){
        return response()->json([
        'msg'=>'Your shift time is over . Please add new shift to start a job',
        ]);
        }
        ActiveShift::create([
        'rider_id' =>$rider_id,
        'division' =>$division,
        'district' => $district,
        'upazilla' => $upazilla,
        'vehicleType' => $vehicleType,
        'is_online' => $is_online,
        'on_delivery' => $on_delivery,
        'givenStartTime' => $givenStartTime,
        'givenEndTime' => $givenEndTime,
        'bodyTemp' => $bodyTemp,
        'activeDateTime' => $currentDateTime,
        'rider_shift_id' => $rider_shift_id
        ]);
        return response()->json([
        'msg'=>'Congratulation ! your shift is active,Save life and earn money',
        'data'=>$isExistInActiveShift
        ]);
        }
    }
    //Auth user get active shift 
    public function getActiveShift()
    {
        $rider=Auth::guard('rider')->user();
        $rider_id = $rider->id;
        // Convert endDate to Carbon instance
        $currentDate=Carbon::now()->format('Y-m-d');
        $currentTime = Carbon::now()->format('H:i:s');
        $activeShift = ActiveShift::where('rider_id',$rider_id)->whereDate('activeDateTime','=',$currentDate)->whereTime('givenEndTime','>',$currentTime)->first();
        return response()->json([
            'data'=>$activeShift
        ]);
    }
    // Auth user update active shift status online-offline boolean 1 or 0 
    public function updateActiveShiftStatus(Request $request)
    {
        //Validate the request
        $validator = Validator::make($request->all(), [
            'status'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $rider=Auth::guard('rider')->user();
            $rider_id = $rider->id;
            // get status value 
            $status=$request->input('status');
            // Convert endDate to Carbon instance
            $currentDate=Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now()->format('H:i:s');
            $activeShift = ActiveShift::where('rider_id',$rider_id)->whereDate('activeDateTime','=',$currentDate)->whereTime('givenEndTime','>',$currentTime)->first();
            $activeShift->update(['is_online'=>$status]);
            return response()->json([
                'msg'=>'Status update successfully'
            ]);
        }
       
    }
    // remove expire shift automatically  when rider login
    public function expireShift()
    {
        $rider=Auth::guard('rider')->user();
        $rider_id = $rider->id;
         // Convert endDate to Carbon instance
        $currentDateTime=Carbon::now();
         // Format it to only include the date part
        $currentDate = $currentDateTime->format('Y-m-d');
        //get expire shift
        RiderShift::where('endDate', '<', $currentDate)->where('rider_id', $rider_id)->update(['status' => 'Expired']);
        return response()->json([
          'msg'=>'Operation done successfully'
        ]);
    } 
}
