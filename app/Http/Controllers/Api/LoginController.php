<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('The provided credentials are incorrect.')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('voice-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
