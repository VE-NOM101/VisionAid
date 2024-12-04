<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    //
    public function dashboard()
    {
        return view('back.admin.dashboard');
    }

    public function getUsers()
    {
        $users = User::latest()->get();

        return $users;
    }

    public function changeRole($id, Request $request)
    {
        // Validate the role input
        $validated = $request->validate([
            'role' => 'required|integer|in:1,2,4', // Allow only specific roles
        ]);

        $user = User::findOrFail($id); // Throw 404 if user is not found

        $user->update([
            'role' => $validated['role'],
        ]);

        return response()->json(['success' => true]);
    }
}
