<?php

namespace App\Services;

use App\Models\PermissionModel;
use App\Models\Musers;
use App\Models\UserwarehousesModel;

use Carbon\Carbon;
use Larinfo;
/**
 * Class UserService.
 */
class UserService
{
    public function __construct()
    {
        $larinfo                        = Larinfo::getInfo();
        $this->date_now                 = Carbon::now()->format('Y-m-d');
        $this->ip                       = request()->ip();
        $this->info                     = $larinfo['server']['software']['os'];
        $this->timenow                  = Carbon::now()->format('h:i:s');
    }
    public function userRelations()
    {
        return Musers::query();
    }
    public function userRelationPermission($id)
    {
        $users              = Musers::from('db_users as u')
                            ->join('db_roles as r', 'u.role_id', 'r.id')
                            ->join('db_permissions as p', 'r.id', 'p.role_id')
                            ->join('db_store as dbs', 'u.store_id', 'dbs.id')
                            ->where('u.id', $id)
                            ->pluck('p.permissions');
        return $users;
    }
    public function userRoles($id)
    {
        $users              = Musers::from('db_users as u')
                            ->join('db_roles as r', 'u.role_id', 'r.id')
                            ->where('u.id', $id);
        return $users;
    }
    public function usersBymail($email)
    {
        $users              = Musers::where('email', $email)->get();

        return $users;
    }
    public function update(object $data)
    {
        $data['action']             = 'update';
        return $this->updateOrcreate($data);
    }
    public function updateOrcreate(object $data)
    {
        if($data->action == 'create'){
            $checkusers                     = Musers::where(\DB::raw("upper(username)"), strtoupper($data->username))
                                            ->get();
            if(count($checkusers) > 0){
                return response()->json([
                    'message'   => 'This username already exist',
                    'code'  => 400
                ]);
            }
            if(!empty($data->mobile)){
                $checkusersmobile           = Musers::where('mobile', $data->mobile)->get();
    
                if(count($checkusersmobile) > 0){
                    return response()->json([
                        'message'   => 'This Moble Number already exist',
                        'code'  => 400
                    ]);
                }
            }
            if(!empty($data->email)){
                $checkusersemail            = Musers::where('email', $data->email)->get();
                if(count($checkusersemail) > 0){
                    return response()->json([
                        'message'   => 'This Email ID already exist',
                        'code'  => 400
                    ]);
                }
            }
            $users                      = new Musers;

            $users->store_id            = \Auth::user()->store_id;
            $users->created_date        = $this->date_now;
            $users->created_time        = $this->timenow;
            $users->system_ip           = $this->ip;
            $users->system_name         = $this->info;
            $users->created_by          = \Auth::user()->username;
            $users->creater_id          = \Auth::user()->id;
        }elseif($data->action == 'update'){
            $users                      = Musers::find($data->user_id);
            if(!empty($fileurl)){
                $users->profile_picture = $fileurl;
            }else{
                $users->profile_picture = $users->profile_picture;
            }
        }else{
            return response()->json([
                'message'   => 'error insert or update',
                'code'  => 400
            ]);
        }
        $users->username                = $data->username;
        $users->first_name              = $data->first_name;
        $users->last_name               = $data->last_name;
        if(!empty($data->password)){
            $users->password            = bcrypt($data->password);
        }
        $users->member_of               = $data->member_of;
        $users->firstname               = $data->firstname;
        $users->lastname                = $data->lastname;
        $users->mobile                  = $data->mobile;
        $users->email                   = $data->email;
        $users->gender                  = $data->gender;
        $users->dob                     = $data->dob;
        $users->country                 = $data->country;
        $users->state                   = $data->state;
        $users->city                    = $data->city;
        $users->address                 = $data->address;
        $users->postcode                = $data->postcode;
        $users->role_name               = $data->role_name;
        $users->role_id                 = $data->role_id;
        $users->photo                   = $data->photo;
        $users->status                  = isset($data->status) ? $data->status : 1;
        $users->updated_at              = Carbon::now();
        $users->default_warehouse_id    = $data->default_warehouse_id;

        $users->save();

        if(!empty($data->warehouses)){
            foreach ($data->warehouses as $i) {
                $data->merge([
                    'user_id'   => $users->id,
                    'warehouse_id'  => $i
                ]);
                $this->insertWarehouseUsers($request);
            }
        }
        return response()->json([
            'message'   => 'success '.$data->command,
            'code'  => 200
        ]);
    }
    public function insertWarehouseUsers(object $data)
    {
        $checkuserswarehouse            = UserwarehousesModel::where('user_id', $data->user_id);
        if(count($checkuserswarehouse->get())){
            $warehouseusers             = $checkuserswarehouse->delete();
        }
        $warehouseusers                 = new UserwarehousesModel;
        $warehouseusers->user_id        = $data->user_id;
        $warehouseusers->warehouse_id   = $data->warehouse_id;

        $warehouseusers->save();
        return $warehouseusers;
    }
}
