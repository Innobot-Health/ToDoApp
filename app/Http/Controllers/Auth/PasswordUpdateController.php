<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;

class PasswordUpdateController extends Controller
{
    public function edit()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'email' => ['required','email','exists:users,email'],
            'current_password' => ['required'],
            'password' => ['required','confirmed','min:8'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Current password is incorrect']]
            ], 422);
        }

        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => ['password' => ['New password cannot be the same as current password']]
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }
}
