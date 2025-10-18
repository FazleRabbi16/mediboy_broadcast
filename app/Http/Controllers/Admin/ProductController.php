<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Company;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\RequestProduct;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Validator;
use Auth;

class ProductController extends Controller
{
// add company id which is match and organize the json file
public function companyIdAdded(Request $request)
{
    // Step 1: Validate uploaded file
    $validator = Validator::make($request->all(), [
        'file' => 'required|file|mimes:txt,json',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Step 2: Read uploaded file content
    $file = $request->file('file');
    $content = file_get_contents($file->getRealPath());
    $objects = json_decode($content, true);

    if (!is_array($objects)) {
        return response()->json(['error' => 'Invalid JSON format'], 400);
    }

    $updatedObjects = [];

    foreach ($objects as $object) {
        if (!is_array($object) || empty($object)) {
            continue; // Skip invalid or empty object
        }

        // Clean company name: trim, remove invisible characters, lower case
        $companyName = isset($object['companyName']) ? $object['companyName'] : '';

        $companyNameClean = strtolower(trim(preg_replace('/\s+/', ' ', $companyName)));

        // Find company
        $company = Company::whereRaw('LOWER(TRIM(name)) = ?', [$companyNameClean])->first();
        $companyId = $company ? $company->id : 50;

        // Insert company_id before companyName
        $newObject = [];
        foreach ($object as $key => $value) {
            if ($key === 'companyName') {
                $newObject['company_id'] = $companyId;
            }
            $newObject[$key] = $value;
        }

        $updatedObjects[] = $newObject;
    }

    // Step 3: Separate objects based on company_id or "N/A" values
    $naOrDefaultCompany = [];
    $otherCompanies = [];

    foreach ($updatedObjects as $obj) {
        $hasNA = false;

        foreach ($obj as $value) {
            if (is_string($value) && strtoupper(trim($value)) === 'N/A') {
                $hasNA = true;
                break;
            }
        }

        if (($obj['company_id'] ?? 0) == 50 || $hasNA) {
            $naOrDefaultCompany[] = $obj;
        } else {
            $otherCompanies[] = $obj;
        }
    }

    // Merge them back: N/A and company_id = 50 first
    $finalObjects = array_merge($naOrDefaultCompany, $otherCompanies);

    // Step 4: Save final output
    $fileName = 'addCompanyIdAdded.txt';
    Storage::disk('local')->put($fileName, json_encode($finalObjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return response()->json([
        'message' => 'File processed successfully!',
        'file' => $fileName
    ]);
}
// After scrabe medicine details from website this function cross check with products table . Then save productNotFound,NotMatch and Match txt file.
public function matchProductFromScrabeData(Request $request)
{
    // Step 1: Validate uploaded file
    $validator = Validator::make($request->all(), [
        'file' => 'required|file|mimes:txt,json',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Step 2: Read uploaded file content
    $file = $request->file('file');
    $content = file_get_contents($file->getRealPath());
    $objects = json_decode($content, true);

    if (!is_array($objects)) {
        return response()->json(['error' => 'Invalid JSON format'], 400);
    }

    $productMatched = [];
    $productNotMatched = [];
    $productNotFound = [];

    foreach ($objects as $object) {

        // Skip incomplete objects
        if (!isset($object['productName'], $object['quantity'], $object['type'], $object['retail_max_price'])) {
            continue;
        }

        // Normalize uploaded object values
        $objectProductName = strtolower(str_replace(' ', '-', $object['productName']));
        $objectQuantity = strtolower(str_replace(' ', '', $object['quantity']));
        $objectType = strtolower($object['type']);
        $objectRetailPrice = floatval(preg_replace('/[^\d.]/', '', $object['retail_max_price']));

        // Use the first word of the product name for initial DB lookup
        $firstWord = explode(' ', $object['productName'])[0];

        // Fetch candidates from the database
        $matchedProducts = Product::where('productName', 'LIKE', "%{$firstWord}%")
            ->select('productName', 'quantity', 'type', 'retail_max_price')
            ->get();

        if ($matchedProducts->isEmpty()) {
            // Save if no candidates found at all
            $productNotFound[] = $object;
            continue;
        }

        $foundMatch = false;

        foreach ($matchedProducts as $product) {

            // Normalize DB values for matching
            $dbProductName = strtolower(str_replace(' ', '-', $product->productName));
            $dbQuantity = strtolower(str_replace(' ', '', $product->quantity));
            $dbType = strtolower($product->type);
            $dbRetailPrice = floatval($product->retail_max_price);

            // Match check with tolerance for floating point price
            if (
                $dbProductName === $objectProductName &&
                $dbQuantity === $objectQuantity &&
                $dbType === $objectType &&
                abs($dbRetailPrice - $objectRetailPrice) < 0.01
            ) {
                $productMatched[] = [
                    'uploaded' => $object,
                    'matched_with' => [
                        'productName' => $product->productName,
                        'quantity' => $product->quantity,
                        'type' => $product->type,
                        'retail_max_price' => $product->retail_max_price
                    ]
                ];
                $foundMatch = true;
                break;
            }
        }

        // If no valid match found but candidates exist
        if (!$foundMatch) {
            $productNotMatched[] = $object;
        }
    }

    // Step 8: Save results to files
    file_put_contents(
        storage_path('app/productMatch.txt'),
        json_encode($productMatched, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    file_put_contents(
        storage_path('app/productNotMatch.txt'),
        json_encode($productNotMatched, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    file_put_contents(
        storage_path('app/productNotFound.txt'),
        json_encode($productNotFound, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    // Step 9: Return summary
    return response()->json([
        'message' => 'Check completed.',
        'total_uploaded_objects' => count($objects),
        'product_matched' => count($productMatched),
        'product_not_matched' => count($productNotMatched),
        'product_not_found' => count($productNotFound),
        'match_file' => storage_path('app/productMatch.txt'),
        'not_match_file' => storage_path('app/productNotMatch.txt'),
        'not_found_file' => storage_path('app/productNotFound.txt')
    ]);
}
// extract url from json
public function extractUrlsFromJson(Request $request)
{
    $validator = Validator::make($request->all(), [
        'data' => 'required|array'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $urls = [];

    foreach ($request->input('data') as $item) {
        if (isset($item['url'])) {
            $urls[] = $item['url'];
        }
    }
    // Step 8: Save results to files
     // Step 8: Save results to a newline-separated text file
    $filePath = storage_path('app/other-product-not-match-url-only.txt');
    $fileContent = implode(PHP_EOL, $urls);
    file_put_contents($filePath, $fileContent);

    return response()->json([
        'urls' =>"complete",
        'count'=>count($urls)
    ]);
}


// add multiple product
public function addMultipleProduct(Request $request)
{
    // Validate that 'products' is an array of product objects
    $validator = Validator::make($request->all(), [
        'products' => 'required|array',
        'products.*.productName'=> 'required|string',
        'products.*.genericName'=> 'required|string',
        'products.*.retail_max_price'=> 'required|numeric',
        'products.*.cart_qty_inc'=> 'required|integer',
        'products.*.cart_text'=> 'required|string',
        'products.*.unit_in_pack' => 'required|string',
        'products.*.type'=> 'required|string',
        'products.*.quantity'=> 'required|string',
        'products.*.prescription'=> 'required|string',
        'products.*.feature'=> 'required|string',
        'products.*.status'=> 'required|string',
        'products.*.category_id' =>'required|integer',
        'products.*.company_id' =>'required|integer',
        'products.*.description'=> 'nullable|string',
        'products.*.coverImage' => 'nullable|string',  // Adjusted to handle file uploads
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $submitted = [];

    foreach ($request->products as $product) {
        // Create the product in the database
        $submitted[] = Product::create([
            'productName' => $product['productName'],
            'genericName' => $product['genericName'],
            'retail_max_price' => $product['retail_max_price'],
            'cart_qty_inc' => $product['cart_qty_inc'],
            'cart_text' => $product['cart_text'],
            'unit_in_pack' => $product['unit_in_pack'],
            'type' => $product['type'],
            'quantity' => $product['quantity'],
            'prescription' => $product['prescription'],
            'feature' => $product['feature'],
            'status' => $product['status'],
            'description' => $product['description'] ?? null,
            'coverImage' => $product['coverImage']?? null,
            'category_id' => $product['category_id'],
            'company_id' => $product['company_id'],
            'add_by' => Auth::guard('admin')->user()->email,
        ]);
    }

    return response()->json(["msg" => count($submitted) . " products submitted successfully."]);
}
// add multiple image 
public function uploadMultipleProductCoverImages(Request $request)
{
    if ($request->hasFile('coverImages')) {
        foreach ($request->file('coverImages') as $file) {
            $originalName = $file->getClientOriginalName();
            $file->storeAs('public/product_cover_images', $originalName);
        }

        return response()->json(['message' => 'Images uploaded successfully']);
    }

    return response()->json(['message' => 'No images uploaded'], 400);
}

// Admin page product fetch
public function get_products(Request $request)
{
    $perPage = $request->input('perPage',10); // Default to 5 if not passed
    $products = Product::with(['images','category:id,name', 'company:id,name'])
                ->orderBy('id', 'desc')
                ->paginate($perPage);

    return response()->json($products);
}
// Home page product fetch
public function index()
{
    //display only feature 20 product of each category
    $products = Category::with(['products' => function ($query){
        $query->select(
            'id',
            'productName',
            'retail_max_price',
            'quantity',
            'type',
            'feature',
            'coverImage',
            'category_id',
            'company_id')
            ->where('feature','yes')->where('status','active')->orderBy('id', 'asc')->take(8);
    }])->get();
    return $products;
}

public function store(Request $request)
    {
      //Validate the request
        $validator = Validator::make($request->all(), [
            'productName'=> 'required',
            'genericName'=> 'required',
            'retail_max_price'=> 'required',
            'cart_qty_inc'=> 'required',
            'cart_text'=> 'required',
            'type'=> 'required',
            'quantity'=> 'required',
            'prescription'=> 'required',
            'category_id' =>'required',
            'company_id' =>'required',
            'feature'=> 'required',
            'description'=> 'nullable',
            'coverImage'=>'image|nullable',
            'files.*'=>'image|nullable'
        ]);
        // validate error message response
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()]);
        }else{
            // get user details
        $user = Auth::guard('admin')->user();
           //Get all text value
        $productName = $request->input('productName');
        $genericName = $request->input('genericName');
        $retail_max_price = $request->input('retail_max_price');
        $cart_qty_inc = $request->input('cart_qty_inc');
        $cart_text = $request->input('cart_text');
        $unit_in_pack = $request->input('unit_in_pack');
        $type = $request->input('type');
        $quantity = $request->input('quantity');
        $prescription = $request->input('prescription');
        $feature = $request->input('feature');
        $status = $request->input('status');
        $description = $request->input('description');
        $category_id = $request->input('category_id');
        $company_id = $request->input('company_id');
        $coverFileNameToStore=NULL;
        // cover image upload
       if ($request->hasFile('coverImage')) {
        $coverImage = $request->file('coverImage');
        //file extention
        $fileExt = $coverImage->getClientOriginalExtension();
         //file name to store
         $coverFileNameToStore = rand(0,1999)."_".time().".".$fileExt;
         // store path
         $path = $coverImage->storeAs('public/product_cover_images',$coverFileNameToStore);
      }
       $product= Product::create([
            'productName' =>$productName,
            'genericName' => $genericName,
            'retail_max_price' => $retail_max_price,
            'cart_qty_inc' => $cart_qty_inc,
            'cart_text' => $cart_text,
            'unit_in_pack' => $unit_in_pack,
            'type' => $type,
            'quantity' => $quantity,
            'prescription' => $prescription,
            'feature' => $feature,
            'status' => $status,
            'description' => $description,
            'coverImage' => $coverFileNameToStore,
            'category_id' => $category_id,
            'company_id' => $company_id,
            'add_by' => $user->email,
        ]);
       // get current product id
       $product_id = $product->id;
      // extra images of products
        if($request->hasFile('files')){
            $files = $request->file('files');
            //get each file to upload
            foreach ($files as $file) {
                //file extention
                $fileExt = $file->getClientOriginalExtension();
                //file name to store
                $fileNameToStore = rand(0,1999)."_".time().".".$fileExt;
                // store path
                $path = $file->storeAs('public/product_images',$fileNameToStore);
                ProductImage::create([
                    'image'=>$fileNameToStore,
                    'product_id'=>$product_id
                ]);
            }
          }
          return response()->json(["msg"=>"Data Submitted Successfully"]);
    }
}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     // total product add by a user 
public function totalProductByUser()
    {
        $productsByUser = Product::selectRaw('add_by, COUNT(*) as total_products')
        ->groupBy('add_by')
        ->get();
       return response()->json($productsByUser);
        
    }
public function show($id)
    {
        $user_id = Auth::user()->id;
        // Fetch the product with images, category, company, and cartItems relationship
        $product = Product::with(['images', 'category', 'company', 'cartItems' => function ($query) use ($id, $user_id) {
            // Use the where clause to filter cartItems based on product_id and user_id
            $query->where('product_id', $id)
                  ->where('user_id', $user_id);
        }])
        ->find($id);
        
        return $product;
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    // Validate the request
    $validator = Validator::make($request->all(), [
        'productName' => 'required',
        'genericName' => 'required',
        'retail_max_price' => 'required|numeric',
        'cart_qty_inc' => 'required|integer',
        'cart_text' => 'required|string',
        'type' => 'required|string',
        'quantity' => 'required|string',
        'prescription' => 'required|string',
        'feature' => 'required|string',
        'status' => 'required|string',
        'description' => 'nullable',
        'coverImage' => 'nullable|image',
        'files.*' => 'nullable|image'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Find product and related images
    $product = Product::with('images')->findOrFail($id);
    $coverFileNameToStore = $product->coverImage; // Default to current image

    // If a new image is uploaded, store and update it
    if ($request->hasFile('coverImage')) {
        // Optional: delete old image if it exists
        if (!empty($product->coverImage)) {
            Storage::delete("public/product_cover_images/{$product->coverImage}");
        }

        $coverImage = $request->file('coverImage');
        $fileExt = $coverImage->getClientOriginalExtension();
        $coverFileNameToStore = rand(1000, 9999) . "_" . time() . "." . $fileExt;
        $coverImage->storeAs('public/product_cover_images', $coverFileNameToStore);
    }

    // Update product
    $product->update([
        'productName' => $request->productName,
        'genericName' => $request->genericName,
        'retail_max_price' => $request->retail_max_price,
        'cart_qty_inc' => $request->cart_qty_inc,
        'cart_text' => $request->cart_text,
        'unit_in_pack' => $request->unit_in_pack,
        'type' => $request->type,
        'quantity' => $request->quantity,
        'prescription' => $request->prescription,
        'feature' => $request->feature,
        'status' => $request->status,
        'description' => $request->description,
        'coverImage' => $coverFileNameToStore, // will remain same if no new image
        'category_id' => $request->category_id,
        'company_id' => $request->company_id,
    ]);

    // Update gallery images only if new files are uploaded
    if ($request->hasFile('files')) {
        // Delete old images from storage
        foreach ($product->images as $image) {
            if (!empty($image->image)) {
                Storage::delete("public/product_images/{$image->image}");
            }
        }

        // Delete from DB
        ProductImage::where('product_id', $id)->delete();

        // Save new images
        foreach ($request->file('files') as $file) {
            $fileExt = $file->getClientOriginalExtension();
            $fileNameToStore = rand(1000, 9999) . "_" . time() . "." . $fileExt;
            $file->storeAs('public/product_images', $fileNameToStore);

            ProductImage::create([
                'image' => $fileNameToStore,
                'product_id' => $id
            ]);
        }
    }

    return response()->json(["msg" => "Data Updated Successfully"]);
}
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Find product details with product images
        $product = Product::with('images')->find($id);
        $product_images= $product->images;
        //remove cover image
        if($product->coverImage !=NULL){
          Storage::delete("public/product_cover_images/{$product->coverImage}");
        }
        $product->delete();

         //remove all file from derectory
         foreach ($product_images as  $images) {
            $fileName = $images->image;
            if($fileName){
               Storage::delete("public/product_images/{$fileName}");
            }
          }
         ProductImage::where('product_id',$id)->delete();
        return response()->json(['msg'=>'Data Delete Successfully']);
      }

    //Remove Single Image
    public function removeSingleImage(int $id)
    {
      $img = ProductImage::findOrFail($id);
      if($img->image)
      {
        //file delete from directory
        Storage::delete("public/product_images/{$img->image}");
      }
      //file dlete form database
      $img->delete();
      return response()->json(['msg'=>'Data Delete Successfully']);

    }
   //  searh product
   public function search(Request $request)
   {
    $productName = strtolower($request->input('productName'));
    // Run only if input has at least 2 characters
    if (strlen($productName) < 2) {
        return response()->json([]); // or you could return a message or 204 status
    }
    $products = Product::with(['images', 'category:id,name', 'company:id,name'])
        ->select(
            'id',
            'productName',
            'genericName',
            'retail_max_price',
            'unit_in_pack',
            'quantity',
            'type',
            'cart_text',
            'coverImage',
            'category_id',
            'company_id'
        )
        ->where('status', 'active')
        ->whereRaw('LOWER(productName) LIKE ?', [$productName . '%']) // starts with only
        ->orderByRaw('LENGTH(productName)') // shortest first
        ->get();

    return $products;
}
  // seach by admin
  public function adminSearch(Request $request)
  {
    $productName = $request->input('productName');
    // Run only if input has at least 2 characters
    if (strlen($productName) < 2) {
        return response()->json([]); // or you could return a message or 204 status
    }
    $products = Product::with(['images', 'category:id,name', 'company:id,name'])
        ->whereRaw('LOWER(productName) LIKE ?', [strtolower($productName) . '%'])
        ->orderByRaw('LENGTH(productName)') // shortest first
        ->get();

    return $products;
}
  //category base product show
  public function cat_product($cat_id)
  {
     $products = Product::select(['id','productName','retail_max_price','quantity','type','feature','coverImage','category_id','company_id',])->where('category_id',$cat_id)->paginate(20);
    return $products;
  }
  //Request product to add
  public function add_request_product(Request $request)
  {
    //Validate the request
    $validator = Validator::make($request->all(), [
        'productName'=>'required',
        'companyName'=>'required',
        'type'=>'required',
        'image'=>'image|nullable'
    ]);
   // validate error message response
   if ($validator->fails()) {
    return response()->json(['errors'=>$validator->errors()]);
    }else{
      //fetching Pharmacy user details
     $pharmacyUserDetails = Auth::guard('pharmacy')->user();
     $pharmacy_id = $pharmacyUserDetails->pharmacy_id;
     $pharmacy_user_id = $pharmacyUserDetails->id;
     $coverFileNameToStore=NULL;
     //prover cover image fetch from request
     if ($request->hasFile('image')) {
        $image = $request->file('image');
        //file extention
        $fileExt = $image->getClientOriginalExtension();
         //file name to store
         $coverFileNameToStore = rand(0,1999)."_".time().".".$fileExt;
         // store path
         $path = $image->storeAs('public/request_product_cover_images',$coverFileNameToStore);
      }
     // create request product details
     $requestProduct= RequestProduct::create([
        'pharmacy_id'=>$pharmacy_id,
        'productName'=>$request->input('productName'),
        'companyName'=>$request->input('companyName'),
        'type'=>$request->input('type'),
        'pharmacy_user_id'=>$pharmacy_user_id,
        'image'=>$coverFileNameToStore
      ]);
    }
    return response()->json(['msg'=>'Data Submitted Successfully']);
  }

  public function get_request_products()
  {
    $products=RequestProduct::orderBy('id','DESC')->get();
    return $products;
  }
  public function get_own_shop_request_products()
  {
    $pharmacyUserDetails = Auth::guard('pharmacy')->user();
    $pharmacy_id = $pharmacyUserDetails->pharmacy_id;
    $request_products = RequestProduct::where('pharmacy_id',$pharmacy_id)
                        ->orderByDesc('id')
                        ->get();
    return $request_products;
  }
  public function remove_request_product($id)
  {
    //Find product details with product images
    $product = RequestProduct::find($id);
    //remove cover image
    if($product->image !=NULL){
      Storage::delete("public/request_product_cover_images/{$product->image}");
    }
    $product->delete();
  return response()->json(['msg'=>'Data Delete Successfully']);
  }
  // get product details for place order by admin
  public function getProductDetails($id)
  {
    // Fetch the product with images, category, company, and cartItems relationship
    $product = Product::with('images', 'category', 'company')->find($id);
    return $product;  
  }

}
