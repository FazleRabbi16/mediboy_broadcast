<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PharmacyArea;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Validator;

class PharmacyAreaController extends Controller
{

    public function index()
    {
        //Pharmacy available area
        $areas = PharmacyArea::orderBy('division','ASC')->get();
        return $areas;
    }

    public function store(Request $request)
    {
        //store pharmacy available area
         $validator = Validator::make($request->all(), [
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'area'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        //Get all text value
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $area = $request->input('area');
        $area=PharmacyArea::create([
            'division'=>$division,
            'district'=>$district,
            'upazilla'=>$upazilla,
            'area'=>$area
        ]);
        }
        return response()->json(['success'=>'Data Submited Successfully']);
    }

    public function show($id)
    {
        //find single area
        $area = PharmacyArea::find($id);
        return $area;
    }

    public function update(Request $request, $id)
    {
         //update pharmacy available area
         $validator = Validator::make($request->all(), [
            'division'=> 'required',
            'district'=> 'required',
            'upazilla'=> 'required',
            'area'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        //Get all text value
        $division = $request->input('division');
        $district = $request->input('district');
        $upazilla = $request->input('upazilla');
        $area = $request->input('area');
        $area=PharmacyArea::where('id',$id)->update([
            'division'=>$division,
            'district'=>$district,
            'upazilla'=>$upazilla,
            'area'=>$area
        ]);
        }
        return response()->json(['success'=>'Data Updated Successfully']);
    }

    public function destroy($id)
    {
        //find single pharmacy available area
        $category = PharmacyArea::find($id);
        $category->delete();
        return response()->json(['success'=>'Data Deleted Successfully']);
    }
    /*
    --------------------------------------------------------
    Dependent Dropdown For Set Self Pickup(pu) Address
    --------------------------------------------------------
    */
    public function get_divisions()
    {
        $division=PharmacyArea::select('division')->distinct()->get();
        return $division;
    }
    public function get_districts($name)
    {
        $district=PharmacyArea::select('district')->where('division',$name)->distinct()->get();
        return $district;
    }
    public function get_upazilla($name)
    {
        $sub_district=PharmacyArea::select('upazilla')->where('district',$name)->distinct()->get();
        return $sub_district;
    }
    public function get_areas($name)
    {
        $area=PharmacyArea::select('area')->where('upazilla',$name)->distinct()->get();
        return $area;
    }
    // search pharmacy in upazilla
    public function get_pharmacy_in_upazilla(Request $request)
    {
     $division=$request->input('division');
     $district=$request->input('district');
     $upazilla=$request->input('upazilla');
     $pharmacies=Pharmacy::where('division',$division)->where('district',$district)->where('upazilla',$upazilla)->get();
     return response()->json([
      'pharmacies'=>$pharmacies
     ]);
    }
    
}
