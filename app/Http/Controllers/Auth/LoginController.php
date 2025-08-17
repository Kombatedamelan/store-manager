<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'adminPhone' => 'required|exists:users,adminPhone',
            'adminPassword' => 'required',
        ]);

        $user = User::where('adminPhone', $request->adminPhone)->first();

        if (! $user || ! Hash::check($request->adminPassword, $user->adminPassword)) {
            return response()->json([
                'error' => 'Numéro de téléphone ou mot de passe incorrect.',
            ], 401);
        }

        return response()->json([
            'token' => $user->createToken('token_api')->plainTextToken,
            'user' => $user,
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }

}
