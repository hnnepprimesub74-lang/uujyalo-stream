<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhoneLookupController extends Controller
{
    /**
     * Report whether an account already exists for the given phone number.
     * Used by the login/register forms to reveal the "no password yet" option.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        return response()->json([
            'exists' => User::where('phone', $request->string('phone'))->exists(),
        ]);
    }
}
