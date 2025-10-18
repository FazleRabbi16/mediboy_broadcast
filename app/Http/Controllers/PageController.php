<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
class PageController extends Controller
{
    //set page
    public function set_page(Request $request)
  {
     $pageName = $request->input('pageName');
     $content = $request->input('content');
      $is_exist = Page::where('pageName',$pageName)->first();
      if($is_exist)
      {
       $is_exist->update(['content'=>$content]);
      }else{
        Page::create(['pageName'=>$pageName,'content'=>$content]);
      }
    return response()->json([
        'msg'=> "Data submitted successfully",
    ]);
  }
  public function get_page(Request $request)
  {
    $pageName = $request->input('pageName');
    $content = Page::where('pageName',$pageName)->first();
    return response()->json([
        'content'=>$content,
    ]);
  }
}
