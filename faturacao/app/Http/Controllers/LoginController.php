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
            ])
            ->post('https://api.gesfaturacao.pt/api/v1.0.4/login', [
                'username' => $request->input('username'),
                'password' => $request->input('password'),
            ]);

        if ($response->successful()) {

            $data = $response->json();

            session([
                'user.id' => $data['user']['id'],
                'user.name' => $data['user']['name'],
                'user.token' => $data['_token'],
            ]);

            return redirect('/dashboard');
        } else {

            return redirect()->back()->withErrors(['error' => 'Credenciais inválidas']);
        }
    }
}