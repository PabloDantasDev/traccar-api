<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Logout extends Controller
{
    public function index()
    {
        // Verifica se o usuário está autenticado
        if (Auth::check()) {
            // Desloga o usuário
            Auth::logout();
            
        }

        // Redireciona para a página de login
         return redirect()->route('auth-login-basic');
    }
}
