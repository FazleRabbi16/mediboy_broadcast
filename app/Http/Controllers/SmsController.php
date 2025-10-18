<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\Models\Otp;
use App\Models\User;
use App\Models\Rider;
use App\Models\PharmacyUser;
use Validator;

class SmsController extends Controller
{
    private const API_KEY = 'NzAwNDk3MDk3ODE3MzE1MDQ1MDUxODg2NDQ3MQ';
    private const SENDER_ID = '8809617611819';
   //send otp to user
    public function sendOtp(Request $request)
    {
        // Retrieve the phone number from the request
        $phoneNumber = $request->input('number');
        $userIsExist=User::where('phoneNumber',$phoneNumber)->first();
         if(!$userIsExist)
         {
          return response()->json(['message' => 'User not found with this number'], 201);
         }else{
        // Generate a random 4-digit OTP
        $otp = rand(1000, 9999);
       // Build the API URL with dynamic phone number and OTP message
        $url = "https://smsp.durjoysoft.com/api/sms?ApiKey=".self::API_KEY ."&SenderID=".self::SENDER_ID."&number=${phoneNumber}&sms=Your OTP is ${otp} and valid for 120 sec. Visit us at https://mediboy.org";

        try {
            // Send the GET request using Laravel's HTTP client
            $response = Http::get($url);

            // Check if the request was successful (status code 200)
            if ($response->successful()) {
                 Otp::create([
                'phoneNumber' => $phoneNumber,
                'otp' => $otp,
                'expire_at' => now()->addMinutes(2),
                ]);
                return response()->json([
                    'message' => 'OTP sent successfully',
                    // "message"=>$response->body()
                ],200);
            } else {
                // If the response is not successful, return the error message
                return response()->json([
                    'message' => 'Failed to send OTP',
                    // 'error' => $response->body()  // Return the raw response for debugging
                ], 500);
            }
        } catch (\Exception $e) {
            // If there is any exception during the request, handle the error
            return response()->json([
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
         }
    }
    // Send OTP to rider
    public function sendOtpRider(Request $request)
    {
        // Retrieve the phone number from the request
        $phoneNumber = $request->input('number');
        $userIsExist=Rider::where('contact',$phoneNumber)->first();
         if(!$userIsExist)
         {
          return response()->json(['message' => 'User not found with this number'], 201);
         }else{
        // Generate a random 4-digit OTP
        $otp = rand(1000, 9999);
        // Construct the SMS message
        $smsMessage = "(Mediboy) Your OTP is " . $otp . ". It's valid for 60 sec. Visit us at https://mediboy.org";
        // Build the API URL with dynamic phone number and OTP message
        $url = "https://smsp.durjoysoft.com/api/sms?ApiKey=".self::API_KEY ."&SenderID=".self::SENDER_ID."&number=${phoneNumber}&sms=Your OTP is ${otp} and valid for 120 sec. Visit us at https://mediboy.org";

        try {
            // Send the GET request using Laravel's HTTP client
            $response = Http::get($url);

            // Check if the request was successful (status code 200)
            if ($response->successful()) {
                 Otp::create([
                'phoneNumber' => $phoneNumber,
                'otp' => $otp,
                'expire_at' => now()->addMinutes(2),
                ],200);
                return response()->json([
                    'message' => 'OTP sent successfully',
                    // "message"=>$response->body()
                ]);
            } else {
                // If the response is not successful, return the error message
                return response()->json([
                    'message' => 'Failed to send OTP',
                    // 'error' => $response->body()  // Return the raw response for debugging
                ], 500);
            }
        } catch (\Exception $e) {
            // If there is any exception during the request, handle the error
            return response()->json([
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
         }
    }
    // Send OTP to pharmacy
    public function sendOtpPharmacy(Request $request)
    {
        // Retrieve the phone number from the request
        $phoneNumber = $request->input('number');
        $userIsExist=PharmacyUser::where('phoneNumber', $phoneNumber)->first();
         if(!$userIsExist)
         {
          return response()->json(['message' => 'User not found with this number'], 201);
         }else{
        // Generate a random 4-digit OTP
        $otp = rand(1000, 9999);
        // Construct the SMS message
        $smsMessage = "(Mediboy) Your OTP is " . $otp . ". It's valid for 60 sec. Visit us at https://mediboy.org";
        // Build the API URL with dynamic phone number and OTP message
        $url = "https://smsp.durjoysoft.com/api/sms?ApiKey=".self::API_KEY ."&SenderID=".self::SENDER_ID."&number=${phoneNumber}&sms=Your OTP is ${otp} and valid for 120 sec. Visit us at https://mediboy.org";
        try {
            // Send the GET request using Laravel's HTTP client
            $response = Http::get($url);

            // Check if the request was successful (status code 200)
            if ($response->successful()) {
                 Otp::create([
                'phoneNumber' => $phoneNumber,
                'otp' => $otp,
                'expire_at' => now()->addMinutes(2),
                ],200);
                return response()->json([
                    'message' => 'OTP sent successfully',
                    // "message"=>$response->body()
                ]);
            } else {
                // If the response is not successful, return the error message
                return response()->json([
                    'message' => 'Failed to send OTP',
                    // 'error' => $response->body()  // Return the raw response for debugging
                ], 500);
            }
        } catch (\Exception $e) {
            // If there is any exception during the request, handle the error
            return response()->json([
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
         }
    }
  //change
    // Verify OTP for all users
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required|string|max:15',
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $phoneNumber = $request->input('number');
        $otp = $request->input('otp');
        $otpRecord = Otp::where('phoneNumber', $phoneNumber)->where('otp', $otp)->first();

        if ($otpRecord && $otpRecord->expire_at > now()) {
            $otpRecord->update(['expire_at' => now()->addMinutes(1)]);
            return response()->json(['message' => 'OTP verified successfully'], 200);
        } else {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }
    }
}
