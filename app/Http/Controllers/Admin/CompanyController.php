<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Validator;

class CompanyController extends Controller
{

    public function index()
    {
        //Return all company
        $companies = Company::orderBy('name','ASC')->get();
       return $companies;
    }


    public function store(Request $request)
    {
        //store company
        $validator = Validator::make($request->all(), [
            'name'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            //Get all text value
        $name = $request->input('name');
        $company = Company::updateOrCreate(
        ['name' => $name], // The condition to search by 'name'
        ['name' => $name]  // The values to update or create
        );
        }
        return response()->json(['success'=>'Data Submited Successfully']);
    }

    public function show($id)
    {
        //find single compnay
        $company = Company::find($id);
        return $company;
    }

    public function update(Request $request, $id)
    {
        //update company
        $validator = Validator::make($request->all(), [
            'name'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            //Get all text value
        $name = $request->input('name');
        $company=Company::where('id',$id)->update([
            'name'=>$name,
        ]);
        }
        return response()->json(['success'=>'Data Updated Successfully']);
    }

    public function destroy($id)
    {
        //delete a specific company
        $company = Company::find($id);
        $company->delete();
        return response()->json(['success'=>'Data Deleted Successfully']);
    }
    public function search(Request $request)
    {
        // Retrieve the search query from the request
        $search = $request->input('qry');
        // Query to get all companies
        $companiesQuery = Company::latest();
        // If search query is provided, filter the results
        if ($search) {
            $companiesQuery->where('name', 'like', '%' . $search . '%');
            // Add more fields as needed for searching
        }
        // Get companies based on the applied filters
        $companies = $companiesQuery->get();
        // Return the companies
        return $companies;
    }

}
