<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingUnsubscribeController extends Controller
{
    public function unsubscribe(Request $request, User $user): View
    {
        $user->update(['marketing_opt_out' => true]);

        return view('marketing.unsubscribed');
    }
}
