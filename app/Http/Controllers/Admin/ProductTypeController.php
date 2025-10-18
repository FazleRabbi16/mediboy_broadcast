<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductType;
use Validator;
class ProductTypeController extends Controller
{

    public function index()
    {
       $productTypes = ProductType::orderBy('name', 'asc')->get();
       return $productTypes;
    }


    public function store(Request $request)
    {
        //store category
        $validator = Validator::make($request->all(), [
            'name'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            //Get all text value
        $name = $request->input('name');
        $product_type=ProductType::create([
            'name'=>$name,
        ]);
        }
        return response()->json(['success'=>'Data Submited Successfully']);
    }

    public function show($id)
    {
        //find single category
        $product_type = productTypes::find($id);
        return $product_type;
    }

    public function update(Request $request, $id)
    {
        //update category
        $validator = Validator::make($request->all(), [
            'name'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            //Get all text value
        $name = $request->input('name');
        $product_type=ProductType::where('id',$id)->update([
            'name'=>$name,
        ]);
        }
        return response()->json(['success'=>'Data Updated Successfully']);
    }

    public function destroy($id)
    {
        /* Delete Category . !!warning if a category delete all product will be delete under this category */
        $product_type = ProductType::find($id);
        $product_type->delete();
        return response()->json(['success'=>'Data Deleted Successfully']);
    }
}
