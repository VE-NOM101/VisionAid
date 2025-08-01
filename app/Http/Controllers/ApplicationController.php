<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    public function __invoke()
    {
        if (Auth::user()) {
            if (Auth::user()->roles->name == "admin") {
                return view('back.admin.dashboard');
            } else if (Auth::user()->roles->name == "doctor") {
                return view('back.doctor.dashboard');
            } else {
                return view('back.user.dashboard');
            }
        } else {
            return redirect('/backRoute');
        }
    }


    public function getProfileInfo(){
        $user = Auth::user();

        
    }
}
