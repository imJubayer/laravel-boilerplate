<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use app\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::allows('superadmin') || Gate::allows('view-roles')) {
            $roles = Role::latest()->get();
            $response = apiResponse(true, 'Roles' . trans('messages.returnsuccessful'), $roles, 200);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::allows('superadmin') || Gate::allows('add-role')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:roles,name|max:25',
            ]);
            if($validator->fails()){
                $response = apiResponse(false, trans('messages.validationerror'), $validator->errors(), 422);
            } else {
                $role = Role::create([
                    'name' => $request->name,
                    'is_modifiable' => 1
                ]);
                $response = apiResponse(true, 'Roles' . trans('messages.createdsuccessful'), $role, 200);
            }
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }   
        return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        if (Gate::allows('superadmin') || Gate::allows('view-role')) {
            $response = apiResponse(true, 'Role' . trans('messages.returnsuccessful'), $role, 200);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        if (Gate::allows('superadmin') || Gate::allows('edit-role')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:roles,name,' .$role->id. '|max:25'
            ]);
            if($validator->fails()){
                $response = apiResponse(false, trans('messages.validationerror'), $validator->errors(), 422);
            } else {
                if($role->is_modifiable){
                    $role->name = $request->name;
    
                    $role->save();
                    $response = apiResponse(true, 'Role' . trans('messages.updatedsuccessful'), $role, 200);   
                } else {
                    $response = apiResponse(false, 'Role' . trans('messages.cannotbedeletedorupdated'), (object)[], 403);
                }
            }
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }    
        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        if (Gate::allows('superadmin') || Gate::allows('delete-role')) {
            if(User::role($role->name)->get()->count() > 0 || !$role->is_modifiable){
                $response = apiResponse(false, 'Role' . trans('messages.cannotbedeletedorupdated'), (object)[], 403);
            } else {
                $res = $role->delete();
                $response = apiResponse(true, 'Role' . trans('messages.deletedsuccessful'), (object)[], 200);
            }
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }   
        return $response;
    }

    /**
     * Assign user a role
     *
     * @param User $user
     * @param Role $role
     * @return void
     */
    public function assignRole(User $user, Role $role)
    {
        if (Gate::allows('superadmin') || Gate::allows('assign-role')) {
            $user->assignRole($role->name);
            $response = apiResponse(true, 'Role assigned successfully', $user->load('roles'), 200);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Remove role for user
     *
     * @param User $user
     * @param Role $role
     * @return void
     */
    public function removeRole(User $user, Role $role)
    {
        if (Gate::allows('superadmin') || Gate::allows('remove-role')) {
            $user->removeRole($role->name);
            $response = apiResponse(true, 'Role removed successfully', $user->load('roles'), 200);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }
}
