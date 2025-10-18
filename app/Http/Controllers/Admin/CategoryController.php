<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Validator;

class CategoryController extends Controller
{

    public function index()
    {
       $categories = Category::latest()->get();
       return $categories;
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
        $category=Category::create([
            'name'=>$name,
        ]);
        }
        return response()->json(['success'=>'Data Submited Successfully']);
    }

    public function show($id)
    {
        //find single category
        $category = Category::find($id);
        return $category;
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
        $category=Category::where('id',$id)->update([
            'name'=>$name,
        ]);
        }
        return response()->json(['success'=>'Data Updated Successfully']);
    }

    public function destroy($id)
    {
        /* Delete Category . !!warning if a category delete all product will be delete under this category */
        $category = Category::find($id);
        $category->delete();
        return response()->json(['success'=>'Data Deleted Successfully']);
    }
    // user get category
    public function userGetCat()
    {
        $categories = Category::all();
        return response()->json(['categories'=>$categories]);
    }
}
