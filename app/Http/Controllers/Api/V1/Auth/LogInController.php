<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogInRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LogInController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LogInRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'The provided credentials are incorrect',
            ], 422);
        }
        $device = substr($request->userAgent() ?? '', 0, 255);

        return response()->json([
            'access_token' => $user->createToken($device)->plainTextToken,
        ]);

    }
}
