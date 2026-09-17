<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PhoneOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the phone number entry view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'phone' => $request->query('phone'),
        ]);
    }

    /**
     * Send a one-time verification code to the given phone number.
     *
     * @throws ValidationException
     */
    public function store(Request $request, PhoneOtpService $otp): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        if (! User::where('phone', $request->phone)->exists()) {
            return back()->withInput()->withErrors([
                'phone' => __('We could not find an account with that phone number.'),
            ]);
        }

        $otp->send($request->string('phone'));

        return redirect()->route('password.reset', ['phone' => $request->phone])
            ->with('status', __('We texted a 6-digit verification code to your phone.'));
    }
}
