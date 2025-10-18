<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class AreaController extends Controller
{
    /*
     30 Minute Coverage Area
     */
    public function index()
    {
        //display all area
        $areas = Area::latest()->get();
        return response()->json($areas);
    }

    public function store(Request $request)
    {
           //Validate the request
           $validator = Validator::make($request->all(), [
            'division'=> 'required',
            'district'=> 'required',
            'image'=> 'image|required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
         //Get all text value
        $division = $request->input('division');
        $district = $request->input('district');
        $image;
        // cover image upload
       if ($request->hasFile('image')) {
        $image = $request->file('image');
        //file extention
        $fileExt = $image->getClientOriginalExtension();
         //file name to store
         $coverFileNameToStore = rand(0,1999)."_".time().".".$fileExt;
         // store path
         $path = $image->storeAs('public/30min_area_images',$coverFileNameToStore);
      }
      // create new entry
      $area= Area::create([
        'division' =>$division,
        'district' => $district,
        'image' => $coverFileNameToStore
       ]);
    }
    return response()->json(['success'=>'Data Submited Successfully']);
    }

    public function show($id)
    {
        //show single area
        $area = Area::find($id);
        return $area;

    }

    public function update(Request $request, $id)
{
    // Validate the request
    $validator = Validator::make($request->all(), [
        'division' => 'required',
        'district' => 'required',
        'image' => 'image'
    ]);

    // Validate error message response
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()]);
    } else {
        $area = Area::find($id);
        // Get all text value
        $division = $request->input('division');
        $district = $request->input('district');
        $coverFileNameToStore = $area->image; // Default value for the image file name

        // Check if a new image is provided in the update request
        if ($request->hasFile('image')) {
            // Delete the existing image file if it exists
            if ($area->image) {
                Storage::delete("public/30min_area_images/{$area->image}");
            }
            // Upload the new image
            $image = $request->file('image');
            $fileExt = $image->getClientOriginalExtension();
            $coverFileNameToStore = rand(0, 1999) . "_" . time() . "." . $fileExt;
            $path = $image->storeAs('public/30min_area_images', $coverFileNameToStore);
        }

        // Update the area entry
        $area->update([
            'division' => $division,
            'district' => $district,
            'image' => $coverFileNameToStore
        ]);
    }

    return response()->json(['success' => 'Data Updated Successfully']);
}


    public function destroy($id)
    {
        //Find area
        $area = Area::find($id);
        if($area->image){
            Storage::delete("public/30min_area_images/{$area->image}");
          }
        $area->delete();
        return response()->json(['success'=>'Data Deleted Successfully']);
    }
}
