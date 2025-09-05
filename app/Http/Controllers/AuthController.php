<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'plan_id' => 'nullable|exists:plans,id',
            ]);

            $planId = $request->input('plan_id');
            if (!$planId) {
                $freePlan = \App\Models\Plan::where('name', 'free')->first();
                $planId = $freePlan ? $freePlan->id : null;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'plan_id' => $planId,
            ]);

            Auth::login($user);
            // Create Sanctum token for API clients
            $token = $user->createToken('api_token')->plainTextToken;

            $user->load('permissions');
            return response()->json([
                'user' => $user,
                'permissions' => $user->permissions->pluck('name'),
                'token' => $token,
                'message' => 'Registration successful'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Registration failed'
            ], 422);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        $user = Auth::user();
        // Create Sanctum token for API clients
        $token = $user->createToken('api_token')->plainTextToken;

        $user->load('permissions');
        return response()->json([
            'user' => $user,
            'permissions' => $user->permissions->pluck('name'),
            'token' => $token,
            'message' => 'Login successful'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    public function user(Request $request)
    {
        $user = Auth::user();
        $user->load('permissions');
        return response()->json([
            'user' => $user,
            'permissions' => $user->permissions->pluck('name'),
        ]);
    }
}
