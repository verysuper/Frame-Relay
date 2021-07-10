<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RequestTokenController extends Controller
{
    public function requestToken(Request $request)
    {
        $response = Http::asForm()->post(env('APP_URL').'/oauth/token', [
            'grant_type' => 'password',
            'client_id' => '2',
            'client_secret' => '8QgXdMdrCIKNtzyR9LL5CbH1tYL0jyKWoEXfVgNy',
            'username' => $request->username,
            'password' => $request->password,
        ]);
        return $response->json();
    }
}
