<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Validator;

class ReportController extends Controller
{

    public function index()
    {
        //get all report
        $reports = Report::all();
        return $reports;
    }

    public function store(Request $request)
    {
        //store single report
        $validator = Validator::make($request->all(), [
            'subject'=> 'required',
            'message'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            //Get all text value
        $subject = $request->input('subject');
        $message = $request->input('message');
        $report=Report::create([
            'subject'=>$subject,
            'message'=>$message,
        ]);
        }
        return response()->json(['msg'=>'Data Submited Successfully']);
    }

    public function show($id)
    {
        //single report
        $report = Report::find($id);
        return $report;
    }

    public function update(Request $request, $id)
    {
        //update single report
        $validator = Validator::make($request->all(), [
            'subject'=> 'required',
            'message'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            //Get all text value
        $subject = $request->input('subject');
        $message = $request->input('message');
        $report=Report::where('id',$id)->update([
            'subject'=>$subject,
            'message'=>$message,
        ]);
        }
        return response()->json(['msg'=>'Data Updated Successfully']);
    }


    public function destroy($id)
    {
        //find resource
        $report = Report::find($id);
        $report->delete();
        return response()->json(['msg'=>'Data Deleted Successfully']);
    }
}
