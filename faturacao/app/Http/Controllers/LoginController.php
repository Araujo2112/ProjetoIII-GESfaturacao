<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class LoginController extends Controller
{
    public function process(Request $request)
    {
        $response = Http::asForm() 
            ->withHeaders([
                'accept' => 'application/json',
                'Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiMiIsInVzZXJuYW1lIjoiZGVtbyIsImNyZWF0ZWQiOiIyMDI1LTEwLTIxIDE2OjU1OjU3In0.RilgfWzF6_GK6Ue2MOP18ieapaekkiGD6WVNuA3kLSE',
            ])
            ->post('https://api.gesfaturacao.pt/api/v1.0.4/login', [
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ]);

        if ($response->successful()) {

            session(['user' => $response->json()]);
            return redirect('/dashboard');
        } else {

            return redirect()->back()->withErrors(['error' => 'Credenciais inválidas']);
        }
    }
}
