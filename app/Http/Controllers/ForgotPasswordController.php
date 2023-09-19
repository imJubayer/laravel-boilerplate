<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return apiResponse(false, trans('messages.validationerror'), $validator->errors(), 422);
        }

        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );
        
        return $response == Password::RESET_LINK_SENT
            ? apiResponse(true, trans('messages.resetlinkemail'), (object)[])
            : apiResponse(false, trans('messages.resetlinksendfail'), (object)[], 422);

            
    }

    protected function broker()
    {
        return Password::broker();
    }

}
