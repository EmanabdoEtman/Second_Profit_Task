<?php

namespace App\Http\Controllers\Api;

use App\User;use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    
      public function register(Request $Request)
    { 

     $rules = [
            'first_name'=>"required",
            'last_name'=>"required",
            'phone_number'=>"required|unique:users|digits_between:10,15|numeric",
            'email'=>"required|email|unique:users",
            'password'=>"required",
            'country_code'=>"required|in:EG,USA",
            'gender'=>"required|in:male,female",
            'birthdate'=>"required|date|date_format:Y-m-d|before:".date('Y-m-d'), 
    ];

    $customMessages = [        
        "first_name.required" => "{'error':'blank'}",
        "last_name.required" => "{'error':'blank'}",
        "phone_number.required" => "{'error':'blank'}",
        "phone_number.unique" => "{'error':'taken'}",
        "phone_number.digits_between" => "{'error':'too_long', 'count': '15'}", 
        "phone_number.numeric" => "{'error':'not_a_number'}",
        'email.required' => '{error:blank222}',
        'email.email' => '{error:invalid}', 
        "email.unique" => "{'error':'taken'}",
        "country_code.required" => "{'error':'blank'}",
        "country_code.in" => "{'error':'inclusion(EG,USA)'}",
        "gender.required" => "{'error':'blank'}",
        "gender.in" => "{'error':'inclusion(male,female)'}",
        "birthdate.required" => "{'error':'blank'}",
        "birthdate.date" => "{'error':'must be date'}",
        "birthdate.date_format" => "{'error':'date format(1988-03-29)'}",
        "birthdate.before" => "{'error':'in_the_future'}",
        "avatar.required" => "{'error':'blank'}",
        "avatar.mimes" => "{'error':'invalid_content_type'}",
    ]; 
 

    $Request['password']=Hash::make($Request['password']);

                if ($Request->file('avatar')) {
                    $file = $Request->file('avatar');
                    $path = 'storage/';
                    $filename = time() . '.' . $file->getClientOriginalName();
                    $file->move($path, $filename);
                    $Request['avatar'] = $path . $filename;
                }
    $validateData=$this->validate($Request, $rules, $customMessages);
 

        $user = User::create($validateData);
        $accessToken = $user->createToken('authToken')->accessToken;

        return response(['user'=>$user , 'access_token'=>$accessToken]);
    }




      public function login (Request $request) {
        $user = User::where('phone_number', $request->phone_number)->first();
        if ($user) {
             if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                 
                $response = ["Status" =>'404' ,"message" =>'success'  ,'user' => $user,'token' => $token];
                return response($response);
            } else {
                $response = ["Status" =>'404'  ,"message" => "Password mismatch"];
                 return response($response);
            }
        } else {
            $response = ["Status" =>'404'  ,"message" =>'User does not exist'];
            return response($response);
        }
    }




      public function check_authorization (Request $request) { 
        $user = User::where('phone_number', $request->phone_number)->first();
        if ($user) { 
                $response = ["Status" =>'200'  ,"message" =>'success'  ,'user' => $user];
                return response($response, 200); 
        } else {
            $response = ["Status" =>'404'  ,"message" =>'bad request'];
            return response($response);
        }
    }
}
