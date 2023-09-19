<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Gate;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::allows('superadmin') || Gate::allows('view-permissions')) {
            $permissions = Permission::with('roles')->latest()->get();
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return apiResponse(true, 'Permissions' . trans('messages.returnsuccessful'), $permissions, 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::allows('superadmin') || Gate::allows('add-permission')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:permissions,name|max:25',
            ]);
            if($validator->fails()){
                $response = apiResponse(false, trans('messages.validationerror'), $validator->errors(), 422);
            } else {
                $permission = Permission::create([
                    'name' => $request->name
                ]);
                $response = apiResponse(true, 'Permission' . trans('messages.createdsuccessful'), $permission, 200);
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
    public function show(Permission $permission)
    {
        if (Gate::allows('superadmin') || Gate::allows('view-permission')) {
            $response = apiResponse(true, 'Permission' . trans('messages.returnsuccessful'), $permission->load('roles'), 200);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        if (Gate::allows('superadmin') || Gate::allows('edit-permission')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:permissions,name,' . $permission->id .'|max:25',
            ]);
            if($validator->fails()){
                $response = apiResponse(false, trans('messages.validationerror'), $validator->errors(), 422);
            } else {
                $permission->name = $request->name;
    
                $permission->save();
                $response = apiResponse(true, 'Permission' . trans('messages.updatedsuccessful'), $permission, 200);
            }
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        if (Gate::allows('superadmin') || Gate::allows('delete-permission')) {
            $res = $permission->delete();
            $response = apiResponse(true, 'Permission' . trans('messages.deletedsuccessful'), (object)[], 200); 
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }
    /**
     * Give permission to role
     *
     * @param Permission $permission
     * @param Role $role
     * @return void
     */
    public function givePermission(Permission $permission, Role $role)
    {
        if (Gate::allows('superadmin') || Gate::allows('give-permission')) {
            $res = $permission->assignRole($role);
            $response = apiResponse(true, trans('messages.permissions.added'), (object)[], 200);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Revoke permission to role
     *
     * @param Permission $permission
     * @param Role $role
     * @return void
     */
    public function revokePermission(Permission $permission, Role $role)
    {
        if (Gate::allows('superadmin') || Gate::allows('revoke-permission')) {
            $role->revokePermissionTo($permission);
            $response = apiResponse(true, trans('messages.permissions.revoked'), (object)[], 200);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

}