<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
   // AuthController

public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'employee_id' => 'required|string|unique:users',
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|unique:users',
        'password' => 'required|string|min:8|confirmed', 
        'phone' => 'required|string|max:20',
        'store_location' => 'nullable|string|max:255',
        'photo_profile' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = User::create([
        'employee_id' => $request->employee_id,
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone' => $request->phone,
        'store_location' => $request->store_location ?? null,
        'photo_profile' => $request->file('photo_profile')
            ? $request->file('photo_profile')->store('photos', 'public')
            : null,
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'data' => new UserResource($user),
        'access_token' => $token,
        'token_type' => 'Bearer',
    ], 201);
}


    public function login(Request $request)
    {
        // Attempt login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Get user and generate token
        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        // Validate update data
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'store_location' => 'nullable|string|max:255',
            'photo_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update data
        $updateData = array_filter([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'store_location' => $request->store_location,
        ]);

        if ($request->hasFile('photo_profile')) {
            $updateData['photo_profile'] = $request->file('photo_profile')->store('photos', 'public');
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
