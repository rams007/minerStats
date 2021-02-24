<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function doRegister(Request $request)
    {
        $credentials = $request->only('firstName', 'lastName', 'email', 'password', 'passwordRepeat');

        if (empty($credentials['firstName'])) {
            return back()->withInput($credentials)->withErrors(['firstName' => 'First name is required']);
        }
        if (empty($credentials['lastName'])) {
            return back()->withInput($credentials)->withErrors(['lastName' => 'Last name is required']);
        }
        if (!isset($credentials['email'])) {
            return back()->withInput($credentials)->withErrors(['email' => 'Email is required']);
        }

        $validator = Validator::make($credentials, [
            'email' => 'email'
        ]);

        if ($validator->fails()) {
            return back()->withInput($credentials)->withErrors(['email' => 'incorrect email']);
        }

        if (empty($credentials['password'])) {
            return back()->withInput($credentials)->withErrors(['password' => 'Password is required']);
        }
        if ($credentials['password'] != $credentials['passwordRepeat']) {
            return back()->withInput($credentials)->withErrors(['password' => 'Password mismatch']);
        }

        $createdUser = User::where('email', $credentials['email'])->first();
        if (!empty($createdUser)) {
            return back()->withInput($credentials)->withErrors(['email' => 'User with this email is already registered']);
        }

        $user = User::create([
            'firstName' => $credentials['firstName'],
            'lastName' => $credentials['lastName'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password'])
        ]);
        Auth::login($user);
        return redirect('/dashboard');
    }

    public function doLogin(Request $request)
    {
        $credentials = $request->only('email', 'password', 'rememberMe');
        if (!isset($credentials['email'])) {
            return back()->withInput($credentials)->withErrors(['email' => 'Email is required']);
        }

        $validator = Validator::make($credentials, [
            'email' => 'email'
        ]);

        if ($validator->fails()) {
            return back()->withInput($credentials)->withErrors(['email' => 'incorrect email']);
        }
        $createdUser = User::where('email', $credentials['email'])->first();
        if (empty($createdUser)) {
            return back()->withInput($credentials)->withErrors(['email' => 'User with this email not registered']);
        }

        if (isset($credentials['rememberMe']) AND ($credentials['rememberMe'] == 'on')) {
            $remember = true;
        } else {
            $remember = false;
        }

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            return redirect('/dashboard');
        } else {
            return back()->withInput($credentials)->withErrors(['email' => 'The provided credentials do not match our records.']);
        }

    }

    public function doPasswordRecovery(Request $request)
    {

        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function doLogout()
    {
        Auth::logout();
        return redirect('/');
    }
}
