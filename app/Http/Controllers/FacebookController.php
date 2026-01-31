<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function index()
    {
        return Socialite::driver('facebook')->scopes(['email', 'ads_management', 'pages_manage_ads', 'pages_read_engagement', 'pages_show_list', 'leads_retrieval'])->redirect();
    }

    public function callback()
    {
        $user = Socialite::driver('facebook')->user();
        dd($user->id);
    }
}
