<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\ApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends ApiController
{
    protected array $providers = ['google'];

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);
        if (!$this->guard()->attempt($credentials)) {
            return $this->errorResponse('Unauthorized', null, 401);
        }
        return $this->respondWithToken(Auth::user());
    }

    public function logout(Request $request): Response
    {
        $this->guard()->logout();
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse([], 'Successfully logged out');
    }

    protected function respondWithToken($user): Response
    {
        Auth::login($user);
        $expirate = now()->addWeek();
        $token = $this->guard()->user()->createToken('authToken', [], $expirate);
        return $this->successResponse(['access_token' => "Bearer {$token->plainTextToken}", 'expires_at' => $expirate->format('Y-m-d H:i:s')]);
    }
}
