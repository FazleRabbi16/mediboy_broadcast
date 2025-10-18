<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*Admin Controller*/
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminFileUploadController;
use App\Http\Controllers\Admin\PlaceController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Pharmacy\SaleController;
use App\Http\Controllers\Pharmacy\PharmacyTransactionController;
use App\Http\Controllers\Pharmacy\PharmacyDashboardController;
use App\Http\Controllers\Admin\AllOrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductTypeController;
use App\Http\Controllers\Admin\PharmacyController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\DeliveryAddressController;
use App\Http\Controllers\Admin\PharmacyAreaController;
use App\Http\Controllers\Admin\SetAgreementController;
use App\Http\Controllers\Admin\DeliveryChargeController;
use App\Http\Controllers\Admin\RequestPharmacyController;
use App\Http\Controllers\Pharmacy\PharmacyAuthController;
use App\Http\Controllers\Pharmacy\ProductStockController;
use App\Http\Controllers\Rider\RiderController;
use App\Http\Controllers\Rider\RiderAuthController;
use App\Http\Controllers\Rider\RiderShiftController;
use App\Http\Controllers\Rider\RiderDeliveryController;
use App\Http\Controllers\Rider\RiderTransactionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
---------------------------------------
ADMIN SECTION START HERE
---------------------------------------
*/
Route::post('/process-urls', [ProductController::class, 'processProductUrls']);
Route::post('/check-duplicate-urls', [ProductController::class, 'checkDuplicateUrls']);
Route::post('/match-product-from-scabe', [ProductController::class, 'matchProductFromScrabeData']);
Route::post('/extract-url-from-json', [ProductController::class, 'extractUrlsFromJson']);
Route::post('/add-company-id', [ProductController::class, 'companyIdAdded']);
Route::post('/update-cImg', [ProductController::class, 'updateProductCoverImages']);
Route::get('/raju_added_products', [ProductController::class, 'get_products_raju']);
Route::get('/get_duplicate_product', [ProductController::class, 'get_duplicate_product']);




Route::post('admin/login',[AdminAuthController::class,'login']);
Route::get('tutorial',[AdminFileUploadController::class,'getTutorial']);
Route::middleware('auth:admin')->group(function () {
    //Admin auth part
    Route::post('admin/register',[AdminAuthController::class,'register']);
    Route::get('admin/userdetails',[AdminAuthController::class,'userDetails']);
    Route::post('admin/update_password',[AdminAuthController::class,'updatePassword']);
    Route::post('admin/update_profile',[AdminAuthController::class,'updateProfile']);
    Route::get('admin/all_user',[AdminAuthController::class,'allUser']);
    Route::post('admin/logout',[AdminAuthController::class,'logout']);
    Route::post('admin/remove_user',[AdminAuthController::class,'removeUser']);
    //Dashboard route
    Route::get('admin/dashboard_highlite',[DashboardController::class,'all_highlites']);
    Route::get('admin/dashboard_today_active_shift',[DashboardController::class,'riderActiveShiftToday']);
    Route::get('admin/active_order',[DashboardController::class,'activeOrder']);
    Route::get('admin/active_delivery',[DashboardController::class,'activeDelivery']);
    Route::get('admin/rider_neg_wallet',[DashboardController::class,'riderNegWallet']);
    Route::get('admin/rider_today_open_shift',[DashboardController::class,'riderOpenShift']);
    // Route::get('today_order_details',[DashboardController::class,'get_today_orders']);
    //User route
    Route::get('user_highlite',[UserController::class,'user_header_highlite']);
    Route::get('all_users',[UserController::class,'get_users_details']);
    Route::post('add_user',[UserController::class,'add_user']);
    Route::post('update_user_status',[UserController::class,'updateStatus']);
    Route::get('user/search',[UserController::class,'search']);
    Route::post('user/filter',[UserController::class,'filter']);
    Route::post('user_prescription',[UserController::class,'get_user_prescriptions']);
    Route::post('deleteprescription',[UserController::class,'destroyMultiple']);
    Route::post('user_agreement',[UserController::class,'set_user_agreement']);
    Route::get('user_agreement',[UserController::class,'get_user_agreement']);
    // page setup
    Route::post('set_page',[PageController::class,'set_page']);
    Route::get('get_page',[PageController::class,'get_page']);
    //Request Pharmacy routes
    Route::get('/request_pharmacy',[RequestPharmacyController::class,'index']);
    Route::get('/request_pharmacy/search',[RequestPharmacyController::class,'search']);
    Route::post('/update_request_pharmacy',[RequestPharmacyController::class,'update']);
    Route::post('/request_pharmacy_agreement',[RequestPharmacyController::class,'updateAgreement']);
    Route::delete('/request_pharmacy/{id}',[RequestPharmacyController::class,'destroy']);
    // Add new pharmacy
    Route::post('/add_pharmacy',[PharmacyController::class,'add_new_pharmacy']);
    Route::post('/pharmacy_add_new_user',[PharmacyController::class,'Add_new_user']);
    Route::post('/update_pharmacy/{id}',[PharmacyController::class,'update_pharmacy']);
    Route::post('/pharmacy_business_setup',[PharmacyController::class,'Business_setup']);
    //get pharmacy details
    Route::get('/pharmacy_details',[PharmacyController::class,'get_pharmacy_details']);
    Route::get('/pharmacy_highlite',[PharmacyController::class,'pharmacy_header_highlite']);
    Route::get('/pharmacy/search',[PharmacyController::class,'search']);
    Route::post('/update_pharmacy_status',[PharmacyController::class,'update_pharmacy_status']);
    Route::get('admin/pharmacy/sale_report',[PharmacyController::class,'sale_report']);
    // pharmacy transaction
    Route::post('admin/pharmacy_transaction',[PharmacyTransactionController::class,'makePharmacyTransaction']);
    Route::get('admin/pharmacy_transaction',[PharmacyTransactionController::class,'PharmacyTransaction']);
    //Set Agreement
    Route::post('pharmacy_agreement',[SetAgreementController::class,'storeOrUpdate']);
    Route::get('pharmacy_agreement',[SetAgreementController::class,'getAgreement']);
    //set Agreement for user
    // Route::post('agreement',[SetAgreementController::class,'userStoreOrUpdate']);
    // Route::get('agreement',[SetAgreementController::class,'getUserAgreement']);
    //Product Route
    Route::resource('products',ProductController::class);
    Route::get('get_products',[ProductController::class,'get_products']);
    Route::delete('/product/image/{id}',[ProductController::class,'removeSingleImage']);
    Route::get('admin/product/search',[ProductController::class,'adminSearch']);
    Route::get('admin/product/details/{id}',[ProductController::class,'getProductDetails']);// single product details
    Route::get('/products/category/{id}',[ProductController::class,'cat_product']);
    Route::get('admin/request_products',[ProductController::class,'get_request_products']);
    Route::delete('admin/request_product/{id}',[ProductController::class,'remove_request_product']);
    Route::get('admin/total_product_by_user',[ProductController::class,'totalProductByUser']);
    Route::post('admin/scrape_products', [ProductController::class, 'scrapeProductData']);// scrabe product data
    Route::post('admin/add_mulitple_products', [ProductController::class, 'addMultipleProduct']);// add multiple products
    Route::post('admin/add_mulitple_product_cover_image', [ProductController::class, 'uploadMultipleProductCoverImages']);// add multiple product cover image
    
    //category routes
    Route::resource('categories',CategoryController::class);
    //compnay routes
    Route::resource('companies',CompanyController::class);
    //product types routes
    Route::resource('product_types',ProductTypeController::class);
    //30 min. area section
    Route::resource('areas',AreaController::class);
    //Set Pharmacy Available Area section for Self Pickup and dpendent dropdown for add pharmacy
    Route::resource('pharmacy_areas',PharmacyAreaController::class);
    Route::get('admin/pharmacy_divisions',[PharmacyAreaController::class,'get_divisions']);
    Route::get('admin/pharmacy_districts/{division}',[PharmacyAreaController::class,'get_districts']);
    Route::get('admin/pharmacy_upazilla/{district}',[PharmacyAreaController::class,'get_upazilla']);
    Route::get('admin/pharmacy_area/{upazilla}',[PharmacyAreaController::class,'get_areas']);
    Route::get('admin/search/upazilla_pharmacy',[PharmacyAreaController::class,'get_pharmacy_in_upazilla']);
    //Order section
    Route::get('admin/orders',[AllOrderController::class,'get_orders']);
    Route::get('admin/order_highlite',[AllOrderController::class,'order_highlite_info']);
    Route::get('admin/order/search',[AllOrderController::class,'search']);
    Route::post('order/filter',[AllOrderController::class,'filter']);
    Route::post('admin/update_order_status',[OrderController::class,'updateOrderStatus']);
    Route::post('admin/find_user_for_order',[AllOrderController::class,'searchUser']);// find user for place order
    Route::get('admin/get_pharmacies_for_order',[AllOrderController::class,'getPharmacyForOrderPlace']);// get active pharmacy for place order
    Route::post('admin/place_order',[AllOrderController::class,'place_order']);//place order
    Route::post('admin/analysis_get_order',[AllOrderController::class,'orderAnalysis']);//place order
    
    
    //Set Report Available Area section
    Route::resource('reports',ReportController::class);
    //Set  Available Place section for delivery-to-address
    Route::get('admin/get_places',[PlaceController::class,'get_places']);
    Route::post('admin/add_place',[PlaceController::class,'save']);
    Route::delete('admin/remove_place/{id}',[PlaceController::class,'destroy']);
    //set pharmacy available area
    Route::get('admin/divisions',[PlaceController::class,'get_division']);
    Route::get('admin/districts/{division}',[PlaceController::class,'get_district']);
    Route::get('admin/upazila/{district}',[PlaceController::class,'get_sub_district']);
    // Delivery charge set route
    Route::resource('deliverychareges',DeliveryChargeController::class);
    Route::post('free_deliveries',[DeliveryChargeController::class,'free_delivery']);
    Route::get('admin_get_free_delivery',[DeliveryChargeController::class,'get_free_delivery_admin']);
    //Rider section
    Route::get('admin/get_request_rider',[RiderController::class,'getRequestRider']);
    Route::delete('admin/remove_request_rider/{id}',[RiderController::class,'removeRequestRider']);
    Route::post('admin/rider_agreement',[RiderController::class,'setRiderAgreement']);
    Route::get('admin/get_rider_agreement',[RiderController::class,'getRiderAgreement']);
    Route::post('admin/rider_privacy_policy',[RiderController::class,'setRiderPrivacyPolicy']);
    Route::get('admin/rider_privacy_policy',[RiderController::class,'getRiderPrivacyPolicy']);
    Route::post('admin/set_rider_place_commission',[RiderController::class,'setRiderPlaceWithCommission']);
    Route::get('admin/get_rider_place_commission',[RiderController::class,'getRiderPlaceWithCommission']);
    Route::get('admin/rider_division',[RiderController::class,'getRiderDivision']);
    Route::get('admin/rider_district/{division}',[RiderController::class,'getRiderDistrict']);
    Route::get('admin/rider_upazilla/{district}',[RiderController::class,'getRiderUpdazilla']);
    Route::get('admin/rider_willing_city',[RiderController::class,'searchAdminRiderCity']);
    Route::get('admin/all_rider',[RiderController::class,'getRiderForAdmin']);
    Route::get('admin/rider_search',[RiderController::class,'searchRider']);
    Route::delete('admin/remove_rider_willing_city/{id}',[RiderController::class,'removeRidercity']);
    Route::get('admin/update_rider_status',[RiderController::class,'updateRiderStatus']);
    Route::get('admin/remove_rider_single_document',[RiderController::class,'removeRiderSingleDocument']);
    Route::post('admin/add_rider_document',[RiderController::class,'addRiderDocument']);
    Route::post('admin/add_rider',[RiderAuthController::class,'addRider']);
    Route::post('admin/get_deliveries',[RiderDeliveryController::class,'getDeliveryReportForAdmin']);
    Route::get('admin/search_delivery',[RiderDeliveryController::class,'searchDelivery']);
    Route::get('admin/cancel_delivery',[RiderDeliveryController::class,'cancelDelivery']);
    //Rider transaction and wallet 
    Route::get('admin/rider_wallet',[RiderTransactionController::class,'riderWallet']);
    Route::get('admin/search_rider_wallet',[RiderTransactionController::class,'searchWallet']);
    Route::post('admin/rider_transaction',[RiderTransactionController::class,'makeTransaction']);
    Route::get('admin/rider_transaction',[RiderTransactionController::class,'getRiderTnx']);
    // upload
    Route::post('admin/software_url',[AdminFileUploadController::class,'addSoftwareUrl']);
    Route::get('admin/software_url',[AdminFileUploadController::class,'getSoftware']);
    Route::get('admin/rmv_software_url',[AdminFileUploadController::class,'removeSoftware']);
    // tutorial upload
    Route::post('admin/tutorial',[AdminFileUploadController::class,'addTutorial']);
    Route::get('admin/rmv_tutorial',[AdminFileUploadController::class,'removeTutorial']);
    
});


/*
---------------------------------------
PHARMACY SECTION ROUTE HERE
---------------------------------------
*/
// send user otp for password reset
Route::post('pharmacy/send_otp',[SmsController::class,'sendOtpPharmacy']);
Route::post('pharmacy/verify_otp',[SmsController::class,'verifyOtp']);
Route::post('pharmacy/password_reset',[PharmacyAuthController::class,'resetPassword']);
//store request pharmacy route
Route::post('/store_pharmacy_request',[RequestPharmacyController::class,'store']);
Route::post('/pharmacy/login',[PharmacyAuthController::class,'login']);

Route::middleware('auth:pharmacy')->group(function () {
     //Pharmacy User Auth routes
     // Dashboard
     Route::get('pharmacy/dashboard_sotck',[PharmacyDashboardController::class,'stockHighlight']);
     Route::get('pharmacy/todayOrder',[PharmacyDashboardController::class,'getTodayOrder']);
     Route::get('pharmacy/activeOrder',[PharmacyDashboardController::class,'getActiveOrder']);
     Route::get('pharmacy/topsaleProduct',[PharmacyDashboardController::class,'getTopSale']);
    //  Route::get('pharmacy/test',[PharmacyAuthController::class,'test']);
     Route::post('pharmacy/add_user',[PharmacyAuthController::class,'register']);
     Route::get('pharmacy/user_details',[PharmacyAuthController::class,'details']);
     Route::post('pharmacy/user_logout',[PharmacyAuthController::class,'logout']);
     Route::get('pharmacy/user_list',[PharmacyAuthController::class,'get_user_list']);
     Route::post('pharmacy/update_profile',[PharmacyAuthController::class,'updateProfile']);
     Route::delete('pharmacy/user_remove/{id}',[PharmacyAuthController::class,'remove']);
     Route::post('pharmacy/update_user/{id}',[PharmacyAuthController::class,'update_user']);
     Route::post('pharmacy/update_password/',[PharmacyAuthController::class,'updatePassword']);
     //Pharmacy Route
     Route::get('pharmacy/pharmacy_details',[PharmacyController::class,'user_pharmacy_details']);
     Route::get('pharmacy/pu_divisions',[PharmacyAreaController::class,'get_divisions']);
     Route::get('pharmacy/pu_districts/{division}',[PharmacyAreaController::class,'get_districts']);
     Route::get('pharmacy/pu_upazilla/{district}',[PharmacyAreaController::class,'get_upazilla']);
     Route::get('pharmacy/pu_area/{upazilla}',[PharmacyAreaController::class,'get_areas']);
     Route::post('pharmacy/update_pharmacy/{id}',[PharmacyController::class,'update_pharmacy']);
     //product request routes
     Route::post('/request_product',[ProductController::class,'add_request_product']);
     Route::get('/request_products',[ProductController::class,'get_own_shop_request_products']);
     Route::delete('/request_product/{id}',[ProductController::class,'remove_request_product']);
     Route::get('/pharmacy/companies/search',[CompanyController::class,'search']);
     Route::get('/pharmacy/types',[ProductTypeController::class,'index']);
     //stock product by pharmacy
     Route::get('pharmacy/product/search',[ProductController::class,'search']);
     Route::get('product/offsale_details/{product_id}',[SaleController::class,'off_sale_product_details']);
     //Pricing
     Route::post('pharmacy/update_sale_price',[ProductStockController::class,'updateSalePrice']);//update sale price
     Route::post('pharmacy/update_sale_price_type',[ProductStockController::class,'changePriceType']);//update sale price type
     Route::get('pharmacy/current_sale_price_type',[ProductStockController::class,'getCurrentPriceType']);//get current sale price type
     //Route::post('product_details',[ProductStockController::class,'get_product_details']);
     
     Route::post('pharmacy/stock_product',[ProductStockController::class,'stock_product']);
     Route::get('pharmacy/previous_stock_value',[ProductStockController::class,'get_single_product_stock_value']);
     Route::get('pharmacy/today_stock',[ProductStockController::class,'get_today_stock']);
     Route::get('pharmacy/current_stock',[ProductStockController::class,'get_current_stock']);
     Route::get('pharmacy/current_stock/search',[ProductStockController::class,'search_current_stock']);
     Route::get('pharmacy/expire_product',[ProductStockController::class,'expire']);
     Route::post('pharmacy/check_expire_product',[ProductStockController::class,'checkExpireProduct']);
     Route::delete('pharmacy/remove_expire_product/{id}',[ProductStockController::class,'remove_expire_product']);
     Route::post('pharmacy/stock_report',[ProductStockController::class,'stock_report']);
     //Off-line Sale product by pharmacy
    Route::get('pharmacy/off_product_details',[SaleController::class,'off_productDetails']);
    Route::get('pharmacy/off_product_stock_details',[SaleController::class,'stock_details']);
    Route::post('pharmacy/off_sales',[SaleController::class,'off_sale']);
     //On-line Sale product by pharmacy
    Route::get('pharmacy/order_count',[SaleController::class,'order_count']);
    Route::get('pharmacy/search_order',[SaleController::class,'searchOrder']);
    Route::get('pharmacy/online_product_stock_details',[SaleController::class,'online_stockDetails']);
    Route::get('pharmacy/online_product_open_stock_details',[SaleController::class,'online_openBatchStockDetails']);
    Route::post('pharmacy/online_sale',[SaleController::class,'online_sale']);
    Route::post('pharmacy/cancel_order',[OrderController::class,'set_cancel_order']);
    // common for off-Online sale by pharmacy
    Route::get('pharmacy/batch_no',[SaleController::class,'get_new_batch_no']);
    Route::post('pharmacy/open_batch',[SaleController::class,'open_batch']);
    // sale report
    Route::get('pharmacy/sale_report',[SaleController::class,'sale_report']);
    Route::get('pharmacy/search/sale_report',[SaleController::class,'search_sale_report']);
    Route::get('pharmacy/current_sale',[SaleController::class,'currentSale']);
    
    // Route::get('pharmacy/search_order',[SaleController::class,'get_order_details']);
    // Route::get('pharmacy/orders',[SaleController::class,'get_pharmacy_order']);
});


/*
---------------------------------------
USER SECTION ROUTE HERE
---------------------------------------
*/
// send user otp for password reset
Route::post('user/send_otp',[SmsController::class,'sendOtp']);
Route::post('user/verify_otp',[SmsController::class,'verifyOtp']);
Route::post('user/password_reset',[UserAuthController::class,'resetPassword']);

Route::get('/available_areas',[AreaController::class,'index']);
// rider 
Route::post('/rider_request_register',[RiderController::class,'RequestRider']);
Route::get('rider/rider_privacy_policy',[RiderController::class,'getRiderPrivacyPolicy']);
//user auth
Route::post('/register',[UserAuthController::class,'register']);
Route::post('/login',[UserAuthController::class,'login']);
// user toc
Route::get('user/user_agreement',[UserController::class,'get_user_agreement']);
// page setup
Route::get('user/get_page',[PageController::class,'get_page']);
Route::middleware('auth:sanctum')->group(function () {
    //user auth
    Route::get('/userdetails',[UserAuthController::class,'userDetails']);
    Route::post('/update_password',[UserAuthController::class,'updatePassword']);
    Route::post('/update_profile',[UserAuthController::class,'updateProfile']);
    Route::post('/logout',[UserAuthController::class,'logout']);
    // Product section
    Route::get('user/products',[ProductController::class,'index']);
    Route::get('user/product/{id}',[ProductController::class,'show']);
    Route::get('/product/search',[ProductController::class,'search']);
    Route::get('products/category/{id}',[ProductController::class,'cat_product']);
    //cart section
    Route::get('carts',[CartController::class,'allCarts']);
    Route::post('cart',[CartController::class,'addToCart']);
    Route::post('update_cart',[CartController::class,'updateCart']);
    Route::post('remove_cart',[CartController::class,'deleteSingleCartItem']);
    // Free delivery up buy
    Route::get('user/free_delivery',[DeliveryChargeController::class,'get_free_delivery']);
    //prescription section
    Route::get('prescription',[PrescriptionController::class,'index']);
    Route::post('prescription',[PrescriptionController::class,'store']);
    Route::post('deleteprescriptions',[PrescriptionController::class,'destroyMultiple']);
    Route::delete('deleteprescription/{id}',[PrescriptionController::class,'removePresription']);
    Route::post('selectprescription',[PrescriptionController::class,'selectPrescription']);
    Route::post('uploadselectprescription',[PrescriptionController::class,'uploadSelect']);
     //user get category
    Route::get('user/category',[CategoryController::class,'userGetCat']);
    //address section
    Route::resource('addresses', DeliveryAddressController::class);
    Route::post('active_delivery_address',[DeliveryAddressController::class,'set_active_delivery_address']);
    Route::get('active_delivery_address_details',[DeliveryAddressController::class,'get_active_delivery_address_details']);
    Route::post('pickup_address',[DeliveryAddressController::class,'set_pickup_address']);
    Route::get('pickup_address',[DeliveryAddressController::class,'get_pickup_address']);
    // Delivery charge
    Route::post('user/delivery_charge',[DeliveryChargeController::class,'get_user_delivery_charge']);
    //Dependent Dropdown for delivery To
    Route::get('user/divisions',[PlaceController::class,'get_division']);
    Route::get('user/districts/{division}',[PlaceController::class,'get_district']);
    Route::get('user/upazillas/{district}',[PlaceController::class,'get_sub_district']);
    //Dependent Dropdown for Self Pickup(pu)
    Route::get('user/pu_divisions',[PharmacyAreaController::class,'get_divisions']);
    Route::get('user/pu_districts/{division}',[PharmacyAreaController::class,'get_districts']);
    Route::get('user/pu_upazillas/{district}',[PharmacyAreaController::class,'get_upazilla']);
    Route::get('user/pu_area/{upazilla}',[PharmacyAreaController::class,'get_areas']);
    //Order section
    Route::post('get_self_pickup_pharmacy',[OrderController::class,'self_pickup_pharmacy']);// get self pickup pharmacy
    Route::post('find_pharmacy_in_area',[OrderController::class,'find_pharmacy_in_area']);// for home delivery in area
    Route::post('find_pharmacy_all_bd',[OrderController::class,'find_pharmacy_all_bd']);//for home delivery all bd
    Route::post('place_order',[OrderController::class,'place_order']);
    Route::get('my_orders',[OrderController::class,'get_my_order']);
    Route::post('cancel_order',[OrderController::class,'set_cancel_order']);
     


    // Route::get('order_helper_data',[OrderController::class,'OrderHelper']);
    // Route::get('cancel_orders',[OrderController::class,'get_modify_order']);
    // Route::get('report_orders',[OrderController::class,'get_report_order']);
    // Route::post('report_order',[OrderController::class,'set_report_order']);
    // Route::post('update_order_status',[OrderController::class,'updateOrderStatus']);
});
/*
------------------
Rider Section
--------------------
*/ 
// get rider app
Route::get('/rider_app',[RiderController::class,'gerRiderApp']);
// send rider otp for password reset
Route::post('rider/send_otp',[SmsController::class,'sendOtpRider']);
Route::post('rider/verify_otp',[SmsController::class,'verifyOtp']);
Route::post('rider/password_reset',[RiderAuthController::class,'resetPassword']);
Route::post('rider/login',[RiderAuthController::class,'login']);

Route::middleware('auth:rider')->group(function () {
    // Rider Profile
    Route::post('rider/update_password',[RiderAuthController::class,'updatePassword']);
    Route::post('rider/update_profile_image',[RiderAuthController::class,'updateProfileImage']);
    Route::get('rider/details',[RiderAuthController::class,'details']);
    Route::post('rider/logout',[RiderAuthController::class,'logout']);
    //Rider Shift 
    Route::get('rider/rider_division',[RiderController::class,'getRiderDivision']);
    Route::get('rider/rider_district/{division}',[RiderController::class,'getRiderDistrict']);
    Route::get('rider/rider_upazilla/{district}',[RiderController::class,'getRiderUpdazilla']);
    Route::post('rider/add_shift',[RiderShiftController::class,'addShift']);
    Route::get('rider/get_shift',[RiderShiftController::class,'getUserShift']); 
    Route::post('rider/rmv_shift',[RiderShiftController::class,'removeShift']);
    Route::post('rider/active_shift',[RiderShiftController::class,'activeShift']);
    Route::get('rider/active_shift',[RiderShiftController::class,'getActiveShift']);
    Route::post('rider/update_active_shift',[RiderShiftController::class,'updateActiveShiftStatus']);
    Route::get('rider/expire_shift',[RiderShiftController::class,'expireShift']);
    //get today confirm order to assign rider
    Route::get('rider/active_job',[RiderDeliveryController::class,'activeJob']);
    Route::post('rider/active_job',[RiderDeliveryController::class,'activeJobToDelivery']);
    Route::get('rider/active_delivery', [RiderDeliveryController::class, 'getActiveDelivery']);
    Route::post('rider/pickup',[RiderDeliveryController::class,'pickup']);
    Route::post('rider/delivered',[RiderDeliveryController::class,'delivered']);
    Route::get('rider/delivery_history',[RiderDeliveryController::class,'lastThirtyDelivery']);
    // get transaction and wallet value
    Route::get('rider/transaction',[RiderTransactionController::class,'recentTransactionAndwalletBallance']);
});


