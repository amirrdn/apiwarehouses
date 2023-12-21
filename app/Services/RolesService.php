<?php

namespace App\Services;
use App\Models\RoleModel;
/**
 * Class RolesService.
 */
class RolesService
{
    public function getRoleByusers($role_id)
    {
        return RoleModel::find($role_id);
    }
}
