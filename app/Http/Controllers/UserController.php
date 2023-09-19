<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Profile;
use App\Events\UserRegistered;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Get all list of users
     *
     * @return void
     */
    public function index(Request $request)
    {
        if (Gate::allows('superadmin') || Gate::allows('view-users')) {
            $limit = $request->limit;
            $search = $request->search; // Get the search query from the request

            $users = User::with(['profile', 'roles', 'roles.permissions'])
                ->whereHas('profile', function ($query) use ($search) {
                    $query->where('phone', 'LIKE', '%' . $search . '%'); // Filter based on phone number in the profile
                })
                ->orWhere('name', 'LIKE', '%' . $search . '%')
                ->latest()
                ->paginate($limit);

            $response = apiResponse(true, 'Users' . trans('messages.returnsuccessful'), $users, 200, true);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }

        return $response;
    }


    /**
     * User login to get token
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            $response = apiResponse(false, trans('messages.validationerror'), $validator->errors(), 422);
        } else {
            if(User::where('email', $request->email)->exists()){
                if(!Auth::attempt(['email' => $request->email, 'password' => $request->password, 'status' => 1])){
                    $msg = trans('messages.passwordnotmatch');
                    if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                        $msg = trans('messages.usernotverified');
                    }
                    $response = apiResponse(false, $msg, (object)[], 401);
                } else {
                    /** @var \App\Models\MyUserModel $user **/
                    $user = Auth::user()->load(['roles', 'roles.permissions']);
                    $token = $user->createToken('token')->plainTextToken;
                    $response = apiResponse(true, trans('messages.loggedinsuccessfully'), (object)['token' => $token, 'user' => $user]);
                }
            } else {
                $msg = trans('messages.usernotexist');
                $response = apiResponse(false, $msg, (object)[], 401);
            }
        }
        return $response;
    }

    /**
     * Add new user
     *
     * @param Request $request
     * @return void
     */
    public function register(Request $request){
        if (Gate::allows('superadmin') || Gate::allows('add-user')) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:25',
                'email' => 'required|unique:users,email',
                'password' => 'required',
                'profile_image' => 'mimes:jpeg,png,jpg,gif'
            ]);
            if($validator->fails()){
                $messages = $validator->messages();
                $response = apiResponse(false, trans('messages.validationerror'), $messages->first(), 422);
            } else {
                try {
                    $image_path = null;
                    if($request->file('profile_image')){
                        $fileName = pathinfo($request->file('profile_image')->getClientOriginalName(), PATHINFO_FILENAME) .time();
                        $extension = $request->file('profile_image')->getClientOriginalExtension();
                        $image_path = $request->file('profile_image')->storeAs(
                            'image',
                            $fileName . '.' .$extension,
                            'public'
                        );
                    }
                    DB::beginTransaction();
                    $user =  User::create([
                        'name' => $request->input('name'),
                        'email' => $request->input('email'),
                        'password' => Hash::make($request->input('password'))
                    ]);
                    $user->assignRole('operator');
                    if($user){
                        // Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password'), 'status' => 1]);
                        // /** @var \App\Models\MyUserModel $user **/
                        // $user = Auth::user();
    
                        // Making a profile
                        $userProfile =  Profile::create([
                            'user_id' => $user->id,
                            'first_name' => $request->input('first_name'),
                            'last_name' => $request->input('last_name'),
                            'phone' => $request->input('phone'),
                            'birth' => $request->input('birth'),
                            'gender' => $request->input('gender'),
                            'address' => $request->input('address'),
                            'city' => $request->input('city'),
                            'state' => $request->input('state'),
                            'country' => $request->input('country'),
                            'zipcode' => $request->input('zipcode'),
                            'bio' => $request->input('bio'),
                            'profile_picture' => $image_path,
                        ]);
                        // $token = $user->createToken('token')->plainTextToken;
                    }
                    event(new UserRegistered($user));
                    DB::commit();
                    $response = apiResponse(true, 'Users' . trans('messages.createdsuccessful'), $user);
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
     * Get a specific user
     *
     * @param User $user
     * @return void
     */
    public function show(User $user)
    {
        if (Gate::allows('superadmin') || Gate::allows('view-user')) {
            $user->load(['profile', 'clinics', 'devices', 'diagnoses', 'roles', 'roles.permissions']);
            $response = apiResponse(true, 'User' . trans('messages.returnsuccessful'), $user);
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (Gate::allows('superadmin') || (Auth::check() && Auth::id() == $user->id)) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:25',
                'password' => 'nullable',
                'profile_image' => 'mimes:jpeg,png,jpg,gif'
            ]);

            if ($validator->fails()) {
                $messages = $validator->messages();
                $response = apiResponse(false, trans('messages.validationerror'), $messages->first(), 422);
            } else {
                try {
                    DB::beginTransaction();

                    $user->name = $request->input('name');

                    if ($request->filled('password')) {
                        $user->password = Hash::make($request->input('password'));
                    }

                    $user->save();

                    $user->load('profile');
                    $userProfile = $user->profile;

                    if ($userProfile) {
                        $userProfile->first_name = $request->input('first_name');
                        $userProfile->last_name = $request->input('last_name');
                        $userProfile->birth = $request->input('birth');
                        $userProfile->gender = $request->input('gender');
                        $userProfile->address = $request->input('address');
                        $userProfile->city = $request->input('city');
                        $userProfile->state = $request->input('state');
                        $userProfile->country = $request->input('country');
                        $userProfile->zipcode = $request->input('zipcode');
                        $userProfile->bio = $request->input('bio');

                        if ($request->file('profile_image')) {
                            // Delete the previous profile image
                            Storage::disk('public')->delete($userProfile->profile_picture);

                            $fileName = pathinfo($request->file('profile_image')->getClientOriginalName(), PATHINFO_FILENAME) . time();
                            $extension = $request->file('profile_image')->getClientOriginalExtension();
                            $image_path = $request->file('profile_image')->storeAs(
                                'image',
                                $fileName . '.' . $extension,
                                'public'
                            );
                            $userProfile->profile_picture = $image_path;
                        }

                        $userProfile->save();
                    }

                    DB::commit();

                    $response = apiResponse(true, 'User' . trans('messages.updatedsuccessful'), $user);
                } catch (\Throwable $th) {
                    DB::rollBack();

                    $response = apiResponse(false, $th->getMessage(), (object)[], 409);
                }
            }
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }

        return $response;
    }




    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'oldPassword' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            $response = apiResponse(false, 'validation failed', $validator->errors(), 422);
        } else {
            $user = Auth::user();
            $oldPasswordValid = Hash::check($request->oldPassword, $user->password);
            if($oldPasswordValid){
                $newPassword = Hash::make($request->input('password'));
                $user->update([
                    'password' => $newPassword
                ]);
                $response = apiResponse(true, trans('messages.password_updated'), (object)[], 200);
            } else {
                $response = apiResponse(false, trans('messages.old_password_mismatch'), (object)[], 200);
            }
        }
        return $response;
    }

    /**
     * Change the status of user
     *
     * @param User $user
     * @return void
     */
    public function changeStatus(User $user)
    {
        if (Gate::allows('superadmin') || Gate::allows('manage-user-status')) {
            try {
                $user->status = !$user->status;
                $user->save();
                $msg = $user->status == 1 ? 'User ' . trans('messages.statusdisapproved') : 'User ' . trans('messages.statusdisapproved');
                $response = apiResponse(true, $msg, $user, 200);
            } catch (\Throwable $th) {
                $response = apiResponse(false, $th->getMessage(), (object)[], 409);
            }
        } else {
            $response = abort(403, trans('messages.permissions.denied'));
        }
        return $response;
    }

    /**
     * Get user profile
     *
     * @return void
     */
    public function profile()
    {
        $user = Auth::user()->load(['profile', 'roles', 'roles.permissions']);
        return apiResponse(true, 'Profile' . trans('messages.returnsuccessful') , $user, 200);
    }
}