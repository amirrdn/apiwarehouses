<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UserService;

use Validator;
use JWTFactory;
use JWTAuth;
use Auth;

class AuthController extends Controller
{
    public function login(Request $request, UserService $usr)
    {
        // \Config::set('jwt.user', 'App\Models\Musers');
        // \Config::set('auth.providers.users.model', App\Models\Musers::class);
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                // return response()->json(['error' => 'invalid_credentials'], 401);
                return response()->json([
                    'message'   => 'These credentials do not match our records.',
                    'code'  => 401
                ]);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        $permissions                = $usr->userRelationPermission(\Auth::user()->id);
        $getusers                   = $usr->userRoles(\Auth::user()->id)
                                    ->select('u.*', 'r.role_name')
                                    ->first();
        $request->session()->put('permissions', $permissions);
        $request->session()->put('users', $getusers);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'permissions' => $permissions,
            'users' => $getusers
        ]);
    }
    public function Unauthenticated()
    {
        return response()->json([
            'message'   => 'Unauthenticated',
            'code'  => 401
        ]);
    }
}
