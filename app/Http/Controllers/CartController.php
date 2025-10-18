<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use Auth;

class CartController extends Controller
{
    // Get all carts
    public function allCarts()
    {
      $user_id = Auth::user()->id;
      $carts = Cart::with('product:id,productName,genericName,cart_text,retail_max_price,unit_in_pack,type,quantity,prescription,feature,coverImage')->where('user_id',$user_id)->get();
      return $carts;
    }
    // add to cart
    public function addToCart(Request $request)
    {
      $user_id = Auth::user()->id;
      $product_id = $request->input('product_id');
      $quantity = $request->input('quantity');
  
      // Update the cart item or create a new one if it doesn't exist
      $cartItem = Cart::updateOrCreate(
          ['user_id' => $user_id, 'product_id' => $product_id],
          ['quantity' => $quantity]
      );
  
      return response()->json(['success' =>'Item added successfully']);
    }

    // right now no need it
    public function updateCart(Request $request)
    {
      $user_id = Auth::user()->id;
      $product_id = $request->input('product_id');
      $quantity = $request->input('quantity');
      $existCartItem = Cart::where([
        ['user_id',$user_id],
        ['product_id',$product_id]
      ])->first();
      $existCartItem->update(['quantity'=>$quantity]);
      if($existCartItem->quantity == 0){
        $existCartItem->delete();
      }
      return response()->json(['success'=>'ITEM UPDATED']);
    }

    // delete a single row from cart
    public function deleteSingleCartItem(Request $request)
    {
      $rowId = $request->input('id');
      $cart = Cart::find($rowId);
      $cart->delete();
      return response()->json(['message'=>'ITEM DELETED']);
    }
}
