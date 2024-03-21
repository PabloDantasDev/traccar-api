<?php

namespace App\Http\Controllers\Authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
  public function index(Request $request)
  {
    return view('content.authentications.auth-login-basic');
  }

  public function execute(Request $request)
  {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
      // Autenticação bem-sucedida
      return redirect()->intended('/');
    }

    // Autenticação falhou
    return back()->withErrors(['email' => 'Credenciais inválidas.']);
  }
}
