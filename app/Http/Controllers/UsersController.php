<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UserService;
use App\Services\FilesService;

class UsersController extends Controller
{
    public function view(Request $request, UserService $usr)
    {
        $users                  = $usr->userRelations()
                                ->where('id', \Auth::user()->id)
                                ->with('userstore')
                                ->first();

        return response()->json([
            'message'   => 'success',
            'code'  => 200,
            'data'  => $users
        ]);
    }
    public function update(Request $request, UserService $usr, FilesService $uploads)
    {
        $validator = \Validator::make($request->all(), [
            'username' => 'required',
            'first_name' => 'required',
            'email' => 'required',
        ],[
            'username.required' => 'Username Name is required !',
            'first_name.required' => 'First Name is required !',
            'email.required' => 'Email is required !',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json([
                'message'   => $error,
                'code'  => 400
            ]);
        }
        $filesdb = '';
        $ffiles = $request['files'];
        if (stripos($ffiles, "data:image/") !== false) {
            if($request->has('files')){
                $request->merge([
                    'name'  => $request->customer_name
                ]);
                $filesdb    = $uploads->UploadFiles($request);
            }
        }else{
            $filesdb        = $request['files'];
        }
        $request->merge([
            'profile_picture' => $filesdb
        ]);
        return $usr->update($request);
    }
}
