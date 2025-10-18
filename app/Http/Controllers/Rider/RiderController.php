<?php
namespace App\Http\Controllers\Rider;
use App\Models\RiderRegisterRequest;
use App\Models\Rider;
use App\Models\RiderAgreement;
use App\Models\RiderFile;
use App\Models\Software;
use App\Models\RiderPrivacyPolicy;
use App\Models\RiderShiftPlaceWithCommission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class RiderController extends Controller
{
    // Get rider app
    public function gerRiderApp()
    {
      $os=['android','ios'];
      $app=Software::whereIn('os',$os)->where('user','rider')->get();
      return response()->json(['app'=>$app]);
    }
    //Get all rider for admin
    public function getRiderForAdmin()
    {
      $statusCounts = Rider::selectRaw('status, COUNT(*) as total')
      ->groupBy('status')
      ->get();

      $rider = Rider::with('riderDocuments')->orderBy('id','desc')->paginate(10);
      return response()->json([
        'rider'=>$rider,
        'statusCounts'=>$statusCounts
      ]);
    }
    // store rider request who become a rider
    public function RequestRider(Request $request)
    {
        // validation before update
        $validator = Validator::make($request->all(), [
        'division' => 'required',
        'district' => 'required',
        'vehicle' => 'required',
        'name' => 'required',
        'contact' => 'required',
        'email' => 'required',            
        'eighteenPlus' => 'required',            
        'termsCondition' => 'required',            
        ]);
        // validate error message response
        if ($validator->fails()) {
        return response()->json(['errors'=>$validator->errors()]);
        }else{
            RiderRegisterRequest::create([
                'division'=>$request->input('division'),
                'district'=>$request->input('district'),
                'vehicle'=>$request->input('vehicle'),
                'name'=>$request->input('name'),
                'contact'=>$request->input('contact'),
                'email'=>$request->input('email'),
                'eighteenPlus'=>$request->input('eighteenPlus'),
                'termsCondition'=>$request->input('termsCondition')
            ]);
        }
        return response()->json([
            'success'=>'Your request for a rider has been successfully completed',
            ]);
    }
    // get requested rider who become a rider
    public function getRequestRider()
    {
      $requestRider =  RiderRegisterRequest::orderBy('id','desc')->get();
      return response()->json([
        'data'=>$requestRider
      ]);
    }
    // remove requested rider 
    public function removeRequestRider($id)
    {
        $rider =  RiderRegisterRequest::find($id);
        $rider->delete();
        return response()->json([
            'msg'=>'Rider remove successfully'
        ]);
    }
    //set rider agreement 
    public function setRiderAgreement(Request $request)
    {
      $content = $request->input('content');
      $agreement_exist = RiderAgreement::first();
      if($agreement_exist)
      {
       $agreement_exist->update(['content'=>$content]);
      }else{
        RiderAgreement::create(['content'=>$content]);
      }
      return response()->json([
          'msg'=> "Rider agreement set successfully",
      ]);
    }
    // get rider agreement
    public function getRiderAgreement()
    {
        $content = RiderAgreement::get();
        return response()->json([
            'content'=>$content,
        ]);
    }
    //set rider privacy policy 
    public function setRiderPrivacyPolicy(Request $request)
    {
      $content = $request->input('content');
      $agreement_exist = RiderPrivacyPolicy::first();
      if($agreement_exist)
      {
       $agreement_exist->update(['content'=>$content]);
      }else{
        RiderPrivacyPolicy::create(['content'=>$content]);
      }
      return response()->json([
          'msg'=> "Rider privacy policy set successfully",
      ]);
    }
    // get rider agreement
    public function getRiderPrivacyPolicy()
    {
        $content = RiderPrivacyPolicy::get();
        return response()->json([
            'content'=>$content,
        ]);
    }
    // set rider willing-shift place with commission percent
    public function setRiderPlaceWithCommission(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
        'division'   => 'required',
        'district'   => 'required',
        'upazilla'   => 'required',
        'status'     => 'required',
        'commission' => 'required'
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        $RiderShiftPlaceWithCommission = RiderShiftPlaceWithCommission::updateOrCreate(
            [
            "division"=>$request->input(['division']),
            "district"=>$request->input(['district']),
            "upazilla"=>$request->input(['upazilla']),
            ], 
            [
            "division"=>$request->input(['division']),
            "district"=>$request->input(['district']),
            "upazilla"=>$request->input(['upazilla']),
            "commission"=>$request->input(['commission']),
            "status"=>$request->input(['status']),
            ]
        );
        return response()->json([
          'msg'=>'Rider place and commission percent set successfully'
        ]);
        }
    }
    // get rider willing-shift place with commission percent
    public function getRiderPlaceWithCommission(Request $request)
    {
      $data =RiderShiftPlaceWithCommission::all();
      return response()->json([
        'data'=>$data
      ]);
    }
    // remove rider willing-shift city 
    public function removeRidercity($id)
    {
      $data =RiderShiftPlaceWithCommission::find($id);
      $data->delete();
      return response()->json([
        'msg'=> 'City remove successfully'
      ]);
    }
    // admin update rider status
    public function updateRiderStatus(Request $request)
    {
      $status = $request->input('status');
      $rider=Rider::find($request->input('id'));
      if(!$rider){
        return response()->json([
          'msg'=>'Rider is not exist'
        ]);
      }else{
        $rider->update(['status'=>$status]);
        return response()->json([
          'msg'=>'Rider status update successfully'
        ]);
      }
      
    }
    // admin remove rider single document file
    public function removeRiderSingleDocument(Request $request)
    {
      $riderDocument=RiderFile::find($request->input('id'));
      //remove cover image
      if($riderDocument->file !=NULL){
        Storage::delete("public/rider_files/{$riderDocument->file}");
      }
      $riderDocument->delete();
      return response()->json([
        'msg'=>'Document remove successfully'
      ]);
    }
    // admin add rider doucment only
    public function addRiderDocument(Request $request)
    {
      //Validate the request
      $validator = Validator::make($request->all(), [
        'rider_id'=> 'required',
        'files.*'=>'image|required'
      ]);
      // validate error message response
      if ($validator->fails()) {
          return response()->json(['errors'=>$validator->errors()]);
      }else{
        $rider_id = $request->input('rider_id');
        // extra file of riders
        if($request->hasFile('files')){
          $files = $request->file('files');
          //get each file to upload
          foreach ($files as $file) {
              //file extention
              $fileExt = $file->getClientOriginalExtension();
              //file name to store
              $fileNameToStore = rand(0,1999)."_".time().".".$fileExt;
              // store path
              $path = $file->storeAs('public/rider_files',$fileNameToStore);
              RiderFile::create([
                  'file'=>$fileNameToStore,
                  'rider_id'=>$rider_id
              ]);
        }
      }
      }
      return response()->json([
        'msg'=>"Document uploaded successfully"
      ]);
    }
    // search rider
    public function searchRider(Request $request)
    {
      $id = $request->input('id');
      $rider=Rider::with('riderDocuments')->find($id);
      if($rider)
      {
      return response()->json([
      'rider'=>$rider
      ]);
      }else{
      return response()->json([
      'msg'=>'No rider found with this id'
      ]);
      }
      
    }

    /*
    ======================
    Rider willing city
    ======================
    */
    public function getRiderDivision()
    {
      $division = RiderShiftPlaceWithCommission::select('division')->distinct()->get();
      return $division;
    }
    public function getRiderDistrict($division)
    {
      $district = RiderShiftPlaceWithCommission::select('district')->where('division',$division)->distinct()->get();
      return $district;
    }
    public function getRiderUpdazilla($district)
    {
      $upazilla = RiderShiftPlaceWithCommission::select('upazilla')->where('district',$district)->distinct()->get();
      return $upazilla;
    }
    public function searchAdminRiderCity(Request $request)
    {
      $division=$request->input('division');
      $district=$request->input('district');
      $rider_willing_city = RiderShiftPlaceWithCommission::where('division',$division)->where('district',$district)->get();
      return $rider_willing_city;
    }
    /*
    ======================
    Rider willing city end
    ======================
    */
}
