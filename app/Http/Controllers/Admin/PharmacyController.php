<?php

namespace App\Http\Controllers\Admin;
use App\Models\Pharmacy;
use App\Models\PharmacyUser;
use App\Models\PharmacyAccount;
use App\Models\MSale;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PharmacyBusinessSetup;
use Illuminate\Support\Facades\Storage;
use Validator;
use Auth;

class PharmacyController extends Controller
{
    public function pharmacy_header_highlite()
    {
        $statusCounts = PharmacyBusinessSetup::selectRaw('status, COUNT(*) as count')
                        ->groupBy('status')
                        ->get();
        return $statusCounts;
    }
    //Fetch Pharmacy Details With Esential Data Like , Pharmacy User , Total Product of the pharmacy sell report for admin panel
    public function get_pharmacy_details()
    {
        $pharmacy_details=Pharmacy::with('pharmacy_users','pharmacy_business')->orderBy('id','DESC')->paginate(10);
        return $pharmacy_details;
    }
        // search
    public function search(Request $request)
    {
        $id = $request->input('id');
        $pharmacy = Pharmacy::where('id', $id)
                    ->first();
        if(!$pharmacy)
        {
            return response()->json([
                "msg"=>'Pharmacy not exist'
            ]);
        }else{
            $pharmacy_details=Pharmacy::with('pharmacy_users','pharmacy_business')->where('id',$id)->first();
            return response()->json([
                "pharmacy_details"=>$pharmacy_details
            ]);
        }

    }

    public function add_new_pharmacy(Request $request)
    {
            // validation before update
        $validator = Validator::make($request->all(), [
            'pharmacyName'=> 'required|unique:pharmacies',
            'drug_lic_no'=> 'required',
            'proprietor'=> 'required',
            'contact'=> 'required',
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'area'=> 'required',
            'placeDetails'=> 'required',
            'googleLink'=> 'required',
            'openTime'=> 'required',
            'closeTime'=> 'required',
            'cover_image'=>'image|required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        //Get all text value
        $pharmacyName = $request->input('pharmacyName');
        $drug_lic_no = $request->input('drug_lic_no');
        $proprietor = $request->input('proprietor');
        $contact = $request->input('contact');
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $area = $request->input('area');
        $placeDetails = $request->input('placeDetails');
        $googleLink = $request->input('googleLink');
        $openTime = $request->input('openTime');
        $closeTime = $request->input('closeTime');
        $offDays = $request->input('offDays');
        $securityCode = $request->input('securityCode');
        $coverFileNameToStore;
        // cover image upload
        if ($request->hasFile('cover_image')) {
            $cover_image = $request->file('cover_image');
            //file extention
            $fileExt = $cover_image->getClientOriginalExtension();
            //file name to store
            $coverFileNameToStore = rand(0,1999)."_".time().".".$fileExt;
            // store path
            $path = $cover_image->storeAs('public/pharmacy_cover_images',$coverFileNameToStore);
        }
        $addPharmacy=Pharmacy::create([
            'pharmacyName'=>$pharmacyName,
            'drug_lic_no'=>$drug_lic_no,
            'proprietor'=>$proprietor,
            'contact'=>$contact,
            'division'=>$division,
            'district'=>$district,
            'upazilla'=>$upazilla,
            'area'=>$area,
            'placeDetails'=>$placeDetails,
            'googleLink'=>$googleLink,
            'openTime'=>$openTime,
            'closeTime'=>$closeTime,
            'offDays'=>$offDays,
            'cover_image'=>$coverFileNameToStore
        ]);
        $pharmacy_id = $addPharmacy->id;
        $pharmacyAccount=PharmacyAccount::create([
            'pharmacy_id'=>$pharmacy_id,
            'balance'=>0
        ]);
        }
        return response()->json([
            'id'=>$pharmacy_id,
            'success'=>'Data Submitted Successfully',
        ]);
    }
    public function user_pharmacy_details() 
    {
        $paharmacy_id = Auth::guard('pharmacy')->user()->pharmacy_id;
        $pharmacy = Pharmacy::where('id',$paharmacy_id)->get();
         return response()->json([
            'pharmacy_details'=>$pharmacy,
         ]);
    }
    public function update_pharmacy(Request $request,$id)
    {
        // validation before update
      $validator = Validator::make($request->all(), [
        'pharmacyName'=> 'required',
        'proprietor'=> 'required',
        'contact'=> 'required',
        'division'=> 'required',
        'district'=> 'required',
        'upazilla'=> 'required',
        'area'=> 'required',
        'placeDetails'=> 'required',
        'googleLink'=> 'required',
        'openTime'=> 'required',
        'closeTime'=> 'required',
        'cover_image'=>'image|nullable',
      ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        //Get all text value
        $pharmacyName = $request->input('pharmacyName');
        $proprietor = $request->input('proprietor');
        $contact = $request->input('contact');
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $area = $request->input('area');
        $placeDetails = $request->input('placeDetails');
        $googleLink = $request->input('googleLink');
        $openTime = $request->input('openTime');
        $closeTime = $request->input('closeTime');
        $offDays = $request->input('offDays');
        $coverFileNameToStore=NULL;
        //find cover image
        $pharmacy = Pharmacy::find($id);

        $pharmacy->update([
            'pharmacyName'=>$pharmacyName,
            'proprietor'=>$proprietor,
            'contact'=>$contact,
            'division'=>$division,
            'district'=>$district,
            'upazilla'=>$upazilla,
            'area'=>$area,
            'placeDetails'=>$placeDetails,
            'googleLink'=>$googleLink,
            'openTime'=>$openTime,
            'closeTime'=>$closeTime,
            'offDays'=>$offDays,
        ]);
        if ($request->hasFile('cover_image')) {
            if($pharmacy->cover_image !=NULL){
                Storage::delete("public/pharmacy_cover_images/{$pharmacy->cover_image}");
            }
            $cover_image = $request->file('cover_image');
            //file extention
            $fileExt = $cover_image->getClientOriginalExtension();
            //file name to store
            $coverFileNameToStore = rand(0,1999)."_".time().".".$fileExt;
            // store path
            $path = $cover_image->storeAs('public/pharmacy_cover_images',$coverFileNameToStore);
            $pharmacy->update([
                'cover_image'=>$coverFileNameToStore
            ]);
        }
        }
        return response()->json([
            'pharmacy'=>$pharmacy,
            'success'=>'Data Updated Successfully',
        ]);

    }

    // Add A New User Of a Specific Pharmacy
    public function Add_new_user(Request $request)
    {
        // validation before update
        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'phoneNumber' => 'required|unique:pharmacy_users,phoneNumber',
            'email' => 'required',
            'password' => 'required',
            'role' => 'required',
            'pharmacy_id' => 'required|exists:pharmacies,id',            
      ]);
      // validate error message response
      if ($validator->fails()) {
          return response()->json(['errors'=>$validator->errors()]);
      }else{
        $pharmacy_id = $request->input('pharmacy_id');
        $firstName = $request->input('firstName');
        $lastName = $request->input('lastName');
        $phoneNumber = $request->input('phoneNumber');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');
        $addPharmacyUser=PharmacyUser::create([
            'firstName'=>$firstName,
            'lastName'=>$lastName,
            'phoneNumber'=>$phoneNumber,
            'email'=>$email,
            'password'=>bcrypt($password),
            'role'=>$role,
            'pharmacy_id'=>$pharmacy_id
        ]);
        }
        return response()->json([
            'success'=>'User add Successfully',
         ]);

      }


    // Add Business Setup of a pharmacy
    public function Business_setup(Request $request)
    {
        // validation before update
        $validator = Validator::make($request->all(), [
          'status'=> 'required',
          'feature'=> 'required',
          'comissionPercent'=> 'required',
          'pharmacy_id'=> 'required'
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            $status = $request->input('status');
            $feature = $request->input('feature');
            $comissionPercent = $request->input('comissionPercent');
            $pharmacy_id = $request->input('pharmacy_id');
            // Update or Create new Business setup
            $businessSetup=PharmacyBusinessSetup::updateOrCreate(
        ['pharmacy_id'=>$pharmacy_id],
        ['status'=>$status,'feature'=>$feature,'comissionPercent'=>$comissionPercent]);
            }
            return response()->json([
                'success'=>'Data Submitted Successfully',
            ]);

    }

    public function update_pharmacy_status(Request $request)
    {
        // validation before update
        $validator = Validator::make($request->all(), [
            'pharmacy_id'=> 'required',
            'status'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        //Get all text value
        $pharmacy_id = $request->input('pharmacy_id');
        $status = $request->input('status');
        $existItem = PharmacyBusinessSetup::where('pharmacy_id',$pharmacy_id)->first();
        if($existItem){
           $existItem->update(['status'=>$status]);
          }
        }
        return response()->json([
            'success'=>'Pharmacy Status Submitted Successfully',
         ]);
    }
    public function sale_report(Request $request)
    {
        $srcStartDate = $request->input('srcStartDate');
        $srcEndDate = $request->input('srcEndDate');
        $pharmacy_id = $request->input('pharmacy_id');
        $status=["Delivered"];
        $busines_percentage = PharmacyBusinessSetup::select('comissionPercent')->where('pharmacy_id',$pharmacy_id)->first();
        $sale_report = Msale::with('user:id,firstName,lastName,phoneNumber,division,district,upazilla','saleItems.product:id,productName,type,quantity,coverImage')->where('pharmacy_id',$pharmacy_id)->whereBetween('saleDate', [$srcStartDate, $srcEndDate])->whereIn('status',$status)->get();
        return response()->json([
        'busines_percentage' => $busines_percentage,
        'sale_report' => $sale_report,
        ]);
    }
}
