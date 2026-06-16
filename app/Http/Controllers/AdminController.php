<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Register Admin/User
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:admins,username',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,user',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('admins', 'public');
        }

        $admin = Admin::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'photo' => $photoPath,
        ]);

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'data' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role,
                'photo' => $admin->photo
                    ? asset('storage/' . $admin->photo)
                    : null,
            ]
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'Invalid username or password'
            ], 401);
        }

        // Optional: remove old tokens
        $admin->tokens()->delete();

        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'data' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role,
                'photo' => $admin->photo
                    ? asset('storage/' . $admin->photo)
                    : null,
            ]
        ]);
    }

    /**
     * Logged-in User Profile
     */
    public function profile(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'data' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role,
                'photo' => $admin->photo
                    ? asset('storage/' . $admin->photo)
                    : null,
            ]
        ]);
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        $admin = $request->user();

        $request->validate([
            'username' => 'sometimes|string|unique:admins,username,' . $admin->id,
            'password' => 'nullable|min:6',
            'role' => 'sometimes|in:admin,user',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->filled('username')) {
            $admin->username = $request->username;
        }

        if ($request->filled('role')) {
            $admin->role = $request->role;
        }

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('admins', 'public');
            $admin->photo = $photoPath;
        }

        $admin->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $admin
        ]);
    }

    /**
     * Logout Current Device
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Logout All Devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices'
        ]);
    }
}