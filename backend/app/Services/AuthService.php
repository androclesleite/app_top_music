<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function authenticate(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function logoutAllDevices(User $user): void
    {
        $user->tokens()->delete();
    }

    public function refreshToken(User $user): string
    {
        // Pega o token atual antes de deletar
        $currentToken = $user->currentAccessToken();
        
        if (!$currentToken) {
            throw ValidationException::withMessages([
                'token' => ['Token atual não encontrado.'],
            ]);
        }
        
        // Cria o novo token antes de deletar o antigo
        $newToken = $user->createToken('auth-token')->plainTextToken;
        
        // Agora deleta o token antigo
        $currentToken->delete();
        
        return $newToken;
    }
}