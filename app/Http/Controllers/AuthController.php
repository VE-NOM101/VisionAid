<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;
use App\Http\Requests\ResetPassword;
use App\Models\Disease;
use App\Models\Quicktest;
use App\Models\User;

class AuthController extends Controller
{

    public function loadRegister()
    {
        if (Auth::user()) {
            $route = $this->redirectDash();
            return redirect($route);
        }
        return view('front.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'string|required|min:2',
            'email' => 'string|email|required|max:100|unique:users',
            'address' => 'string|required',
            'password' => 'string|required|confirmed|min:6'
        ]);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->password = Hash::make($request->password);
        $user->remember_token = Str::random(50);
        $user->save();

        return back()->with('success', 'Your Registration has been successfull.');
    }

    public function loadLogin()
    {
        if (Auth::user()) {
            $route = $this->redirectDash();
            return redirect($route);
        }
        return view('front.auth.login');
    }



    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $userCredential = $request->only('email', 'password');
        $throttleKey = $this->throttleKey($request);

        // Check if login should be disabled before incrementing login attempts
        if (Cache::has($throttleKey) && Cache::get($throttleKey) >= 2) {
            Cache::put($throttleKey, true, now()->addMinutes(1));
            return back()->withInput()->with('error', 'Too many login attempts. Please try again in 1 minute.');
        }

        // Attempt login
        if (Auth::attempt($userCredential)) {
            // Clear the login attempts when user successfully logs in
            Cache::forget($throttleKey);

            $route = $this->redirectDash();
            return redirect($route);
        } else {
            // Increment login attempts
            $this->incrementLoginAttempts($throttleKey);

            return back()->withInput()->with('error', 'Username & Password is incorrect');
        }
    }

    protected function incrementLoginAttempts($throttleKey)
    {
        // Increment the count of login attempts
        $attempts = Cache::get($throttleKey, 0);
        $attempts++;
        Cache::put($throttleKey, $attempts, now()->addMinutes(1));
    }

    protected function throttleKey(Request $request)
    {
        return 'login_throttle_' . Str::lower($request->input('email')) . '|' . $request->ip();
    }

    //
    public function redirectDash()
    {
        $redirect = '';

        if (Auth::user() && Auth::user()->role == 1) {
            $redirect = '/_user/dashboard';
        } else if (Auth::user() && Auth::user()->role == 2) {
            $redirect = '/_doctor/dashboard';
        } else if (Auth::user() && Auth::user()->role == 4) {
            $redirect = '/_admin/dashboard';
        } else {
            $redirect = '/';
        }

        return $redirect;
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        return redirect('/');
    }

    public function loadForgot(Request $request)
    {
        return view('front.auth.forgot');
    }
    public function forgot(Request $request)
    {
        $count = User::where('email', '=', $request->email)->count();

        if ($count > 0) {
            $user = User::where('email', '=', $request->email)->first();
            $user->remember_token = Str::random(50);
            $user->save();

            Mail::to($user->email)->send(new ForgotPasswordMail($user));

            return redirect()->back()->with('success', 'Password has been reset. Please check your Spam or junk mail folder.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Not found the email in the system');
        }
    }

    public function getReset($email, $token)
    {
        if (Auth::check()) {
            return redirect('/backRoute');
        }

        $user = User::where('remember_token', '=', $token)->where('email', '=', $email);
        if ($user->count() == 0) {
            abort(404);
        }
        $user = $user->first();
        $data['token'] = $token;
        $data['email'] = $email;
        return view('front.auth.reset', $data);
    }
    public function postReset($email, $token, ResetPassword $request)
    {
        $user = User::where('remember_token', '=', $token)->where('email', '=', $email);
        if ($user->count() == 0) {
            abort(404);
        }
        $user = $user->first();
        $user->password = Hash::make($request->password);
        $user->remember_token = Str::random(50);
        $user->save();
        return redirect('/backRoute')->with('success', 'Password has been reset.');
    }

    public function getAuthenticatedUserRole()
    {
        if (Auth::user()) {
            $user = Auth::user(); // Get the authenticated user
            return response()->json([
                'role' => $user->roles->name, // Assuming roles is a relationship and name is the role name
                'name' => $user->name,
            ]);
        } else {
            return response()->json(['error' => true]);
        }
    }

    public function getMyid()
    {
        if (Auth::user()) {
            return response()->json([
                'id' => Auth::user()->id,
            ]);
        } else {
            return response()->json(['error' => true]);
        }
    }

    public function getInfo() {

        $disease_list = Disease::all();

        // Initialize a frequency array
        $frequencies = [
            'disease_1' => 0,
            'disease_2' => 0,
            'disease_3' => 0,
        ];

        // Fetch all rows from the quicktests table
        $quickTests = Quicktest::all();

        foreach ($quickTests as $test) {
            // Determine the disease with the highest percentage
            $maxPercentage = max(
                $test->percentage_disease_1,
                $test->percentage_disease_2,
                $test->percentage_disease_3
            );

            // Increment the frequency for the disease with the highest percentage
            if ($maxPercentage === $test->percentage_disease_1) {
                $frequencies['disease_1']++;
            } elseif ($maxPercentage === $test->percentage_disease_2) {
                $frequencies['disease_2']++;
            } elseif ($maxPercentage === $test->percentage_disease_3) {
                $frequencies['disease_3']++;
            }
        }

        // Return the frequencies
        return response()->json(['message' => 'Successfully retrieved', 'disease_list' => $disease_list, 'frequencies' => $frequencies]);
    }

}
