<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\Request;
use Validator;

class PlaceController extends Controller
{

    public function get_places()
    {
        //get all places
        $places = Place::all();
        return response()->json(['places'=>$places],200);
    }

   public function save(Request $request)
   {
    // Validate request
    $validator = Validator::make($request->all(), [
        'division'=> 'required',
        'district'=> 'required',
        'upazila'=> 'required', 
        ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()]);
    }

    try {
        // condition check (division, district, upazila)
        $place = Place::updateOrCreate(
            [
                'division' => $request->input('division'),
                'district' => $request->input('district'),
                'upazila'  => $request->input('upazila'),
            ],
            [   
                'division' => $request->input('division'),
                'district' => $request->input('district'),
                'upazila'  => $request->input('upazila'),
            ]
        );

        // response message based on whether it was updated or created
        if ($place->wasRecentlyCreated) {
            return response()->json(['success' => 'Data Submitted Successfully']);
        } else {
            return response()->json(['success' => 'Data Updated Successfully']);
        }

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function destroy($id)
    {
        $place = Place::find($id);
        $place->delete();
        return response()->json(['success'=>'Data Deleted Successfully']);
    }

    /*
    --------------------------------------------------------
    Dependent Dropdown For Set Delivery Address 
    --------------------------------------------------------
    */
    public function get_division()
    {
        $divisions = Place::select('division')->distinct()->get();
        return response()->json(['divisions' => $divisions]);
    }
    public function get_district($name)
    {
        $districts=Place::select('district')->where('division',$name)->distinct()->get();
        return response()->json(['districts' => $districts]);
    }
    public function get_sub_district($name)
    {
        $upazillas=Place::select('upazila')->where('district',$name)->distinct()->get();
        return response()->json(['upazillas' => $upazillas]);
    }
}
