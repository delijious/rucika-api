<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        
        $this->guard = "api"; // add
    }

    public function register(Request $request)
    {
    
        $data = $request->only('username', 'password');
        $validator = Validator::make($data, [
            'username' => 'required|string|max:20|unique:users',
            // 'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $user = User::create([
        	'username' => $request->username,
        	// 'email' => $request->email,
        	'password' => strtoupper(md5($request->password)),
            'user_level'=>'Customer'
        ]);
       
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ]);
    }
    public function authenticate(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $validator = Validator::make($credentials, [
            'username' => 'required|string',
            'password' => 'required|string|min:6|max:50'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
    
        try {

            $user = User::where('username', $request->username)
            ->where('password',md5($request->password))->first();
         
            if (! $user ) {
                return response()->json([ 'success' => false,'message'=>'Login credentials are invalid.' ], 401);
            }
            if (! $token = auth( $this->guard )->login( $user ) ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }

        } catch (JWTException $e) {
    	// return $credentials;
            return response()->json([
                	'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }
 	
        return response()->json([
            'success' => true,
            'type'=>'Bearer',
            'token' => $token,
        ]);
    }
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
}
