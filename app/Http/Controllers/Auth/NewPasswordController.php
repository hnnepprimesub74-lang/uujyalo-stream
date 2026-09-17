<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PhoneOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Display the OTP + new password view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'phone' => $request->query('phone'),
        ]);
    }

    /**
     * Verify the OTP and set a new password for the account.
     *
     * @throws ValidationException
     */
    public function store(Request $request, PhoneOtpService $otp): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (! $user || ! $otp->verify($request->string('phone'), $request->string('otp'))) {
            return back()->withInput($request->only('phone'))->withErrors([
                'otp' => __('That code is invalid or has expired.'),
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
