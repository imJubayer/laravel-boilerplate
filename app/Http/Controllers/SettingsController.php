<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Gate::allows('superadmin') || Gate::allows('view-settings')) {
            $settings = Settings::latest()->get();
            $response = apiResponse(true, 'Settings' . trans('messages.returnsuccessful'), $settings);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(404);
        if (Gate::allows('superadmin') || Gate::allows('add-setting')) {
            $validator = Validator::make($request->all(), [
                'settings_key' => 'required|unique:settings,settings_key|max:25',
                'settings_value' => 'required',
                'settings_type' => 'required|in:json,string,boolean,text,array',
            ]);
            if($validator->fails()){
                $messages = $validator->messages();
                $response = apiResponse(false, trans('messages.validationerror'), $messages->first(), 403);
            } else {
                try {
                    $setting = Settings::create($request->all());
                    $response = apiResponse(true, 'Setting' . trans('messages.createdsuccessful'), $setting, 201);
                } catch (\Throwable $th) {
                    $response = apiResponse(false, $th->getMessage(), (object)[], 409);
                }
            }
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Display the specified resource.
     */
    public function show(Settings $setting)
    {
        if (Gate::allows('superadmin') || Gate::allows('view-setting')) {
            $response = apiResponse(true, 'Setting' . trans('messages.returnsuccessful'), $setting);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Settings $setting)
    {
        if (Gate::allows('superadmin') || Gate::allows('edit-setting')) {
            $validator = Validator::make($request->all(), [
                'settings_key' => 'required|unique:settings,settings_key,'. $setting->id .'|max:25',
                'settings_value' => 'required',
                'settings_type' => 'required|in:json,string,boolean,text,image,number',
            ]);
            if ($request->hasFile('settings_value')) {
                $validator->sometimes('settings_value', 'mimes:jpg,jpeg,png', function ($input) {
                    return $input->settings_value instanceof \Illuminate\Http\UploadedFile;
                });
            }
            if($validator->fails()){
                $response = apiResponse(false, trans('messages.validationerror'), $validator->errors(), 403);
            } else {
                try {
                    if($request->file('settings_value')){
                        $fileName = $setting->settings_key === 'logo' ? 'logo' : pathinfo($request->file('settings_value')->getClientOriginalName(), PATHINFO_FILENAME) .time();
                        $extension = $request->file('settings_value')->getClientOriginalExtension();
                        $image_path = $request->file('settings_value')->storeAs(
                            'image',
                            $fileName . '.' .$extension,
                            'public'
                        );
                    }
                    $setting->settings_key = $request->settings_key;
                    $setting->settings_value = $request->file('settings_value') ? $image_path : $request->settings_value;
                    $setting->settings_type = $request->settings_type;
                    $setting->save();
                    $response = apiResponse(true, 'Setting' . trans('messages.updatedsuccessful'), $setting);
                } catch (\Throwable $th) {
                    $response = apiResponse(false, $th->getMessage(), (object)[], 409);
                }
            }
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Settings $setting)
    {
        abort(404);
        if (Gate::allows('superadmin') || Gate::allows('delete-setting')) {
            $res = $setting->delete();
            $response = apiResponse(true, 'Settings' . trans('messages.deletedsuccessful'), (object)[], 200);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    public function settingsKeyValue()
    {
        if (Gate::allows('superadmin') || Gate::allows('view-settings')) {
            $settings = Settings::latest()->get();
    
            foreach ($settings as $setting) {
                $data[$setting->settings_key] = $setting->settings_value;
            }
            $response = apiResponse(true, 'Settings key value' . trans('messages.returnsuccessful'), $data);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }

        return $response;
    }
}
