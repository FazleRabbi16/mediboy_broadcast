<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Models\SetAgreement;
use Illuminate\Http\Request;
use App\Models\SetUserAgreement;
use App\Http\Controllers\Controller;


class SetAgreementController extends Controller
{
    public function getAgreement()
    {
        $content = SetAgreement::get();
        return $content;
    }

    public function storeOrUpdate(Request $request)
    {
        //store category
        $validator = Validator::make($request->all(), [
            'content'=> 'required',
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
        $content = $request->input('content');
        $firstRow = SetAgreement::first();
        if(empty($firstRow))
        {
            $setContent = SetAgreement::create([
                'content' =>$content
                ]);
        }else{
            $setContent = SetAgreement::where('id',$firstRow->id)->update([
                'content' =>$content
                ]);
        }
    }
    return response()->json(['success'=>'Data Submitted Successfully']);
  }

}
