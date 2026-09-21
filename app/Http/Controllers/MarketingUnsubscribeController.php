<?php

namespace App\Http\Controllers;

use App\Models\MarketingContact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingUnsubscribeController extends Controller
{
    public function unsubscribeUser(Request $request, User $user): View
    {
        $user->update(['marketing_opt_out' => true]);

        return view('marketing.unsubscribed');
    }

    public function unsubscribeContact(Request $request, MarketingContact $contact): View
    {
        $contact->update(['marketing_opt_out' => true]);

        return view('marketing.unsubscribed');
    }
}
