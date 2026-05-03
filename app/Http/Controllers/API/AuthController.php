<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Enum;
use App\Enums\UserRole;
use Illuminate\Validation\Rule;

class AuthController extends BaseController
{
    //

    public function register(Request $request):JsonResponse
    {

    
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);
        if ($validated->fails()){
            return $this->sendError('Validation Error.', $validated->errors()); 
        }
        // $user = \App\Models\User::create($validatedData);
       $validatedData = $validated->validated();
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
        ]);

        return $this->sendResponse($user, 'User register successfully.');

    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if (!auth()->attempt($validatedData)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth()->user();
        //$token = $user->createToken('auth-token')->plainTextToken;
        $token = $user->createToken('auth-token', ['*'], now()->addHours(24))->plainTextToken;

        //return response()->json(['message' => 'User logged in successfully', 'user' => $user, 'token' => $token], 200);
        return $this->sendResponse(['user' => $user, 'token' => $token], 'User logged in successfully');
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return $this->sendResponse([], 'User logged out successfully');
    }

    public function user(Request $request)
    {
        return $this->sendResponse($request->user(), 'User retrieved successfully');
    }

    public function profile(Request $request)
    {
        return $this->sendResponse($request->user(), 'User profile retrieved successfully');
    }
}