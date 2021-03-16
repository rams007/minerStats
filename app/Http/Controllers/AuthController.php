<?php

namespace App\Http\Controllers;

use App\Http\Helpers\HelperController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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

    public function handleGoogleCallback(Request $request)
    {
        try {
            $user = Socialite::driver('google')->user();

            $firstName = 'John';
            $lastName = 'Doe';
            $email = '';
            if ($user->getEmail()) {
                $email = $user->getEmail();
            }
            $user->getName();

            /*
                        var_dump($user->user);
                        var_dump($user);
            */

            if (isset($user->user['given_name'])) {
                $firstName = $user->user['given_name'];

            } else {
                if ($user->getName()) {
                    $firstName = $user->getName();
                }
            }

            if (isset($user->user['family_name'])) {
                $lastName = $user->user['family_name'];
            }

            if (empty($email)) {
                echo 'Email not found';
            } else {

                $user = User::where('email', $email)->first();

                if ($user) {
                    Auth::login($user);
                    return redirect('/dashboard');
                } else {

                    $pass = Str::random(8);
                    $user = User::create([
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'email' => $email,
                        'password' => Hash::make($pass)
                    ]);
                    Auth::login($user);

                    //@todo send message to user with new password

                    $message = view('mail.socialRegs', ['email' => $request->email, 'password' => $pass,
                        'provider' => 'google'])->render();

                    HelperController::sendMail($email, 'support@miner-stats.com', 'Registration on MinerStats.com', $message);

                    return redirect('/dashboard');

                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    public function handleFBCallback(Request $request)
    {
        try {

            $user = Socialite::driver('facebook')->user();

            $firstName = 'John';
            $lastName = ' ';
            $email = '';
            if ($user->getEmail()) {
                $email = $user->getEmail();
            }
            $user->getName();


            /* var_dump($user->user);
             var_dump($user);
 */

            if (isset($user->user['name'])) {
                $firstName = $user->user['name'];

            } else {
                if ($user->getName()) {
                    $firstName = $user->getName();
                }
            }


            if (empty($email)) {
                echo 'Email not found';
            } else {

                $user = User::where('email', $email)->first();

                if ($user) {
                    Auth::login($user);
                    return redirect('/dashboard');
                } else {

                    $pass = Str::random(8);
                    $user = User::create([
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'email' => $email,
                        'password' => Hash::make($pass)
                    ]);
                    Auth::login($user);

                    //@todo send message to user with new password

                    $message = view('mail.socialRegs', ['email' => $request->email, 'password' => $pass,
                        'provider' => 'FB'])->render();

                    HelperController::sendMail($email, 'support@miner-stats.com', 'Registration on MinerStats.com', $message);

                    return redirect('/dashboard');

                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function loginAndroid(Request $request)
    {
        try {
            Log::debug(print_r($request->all(), true));

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'device_name' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            return response()->json(['error' => false, 'token' => $user->createToken($request->device_name)->plainTextToken]);
        } catch (\Throwable $e) {
            return response()->json(['error' => true, 'msg' => $e->getMessage()]);
        }

    }
}
