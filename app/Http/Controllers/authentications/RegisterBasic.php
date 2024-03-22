<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RegisterBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }


 public function registra(Request $request)
  {
    $data = [
      'identificador' => $request->identificador,
      'email' => $request->email,
      'password' => $request->password,
    ];

    $validate = Validator::make($data, 
    [
      'identificador' => 'required',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|min:6'
    ],
    [
      'identificador' => 'Identificador inválido!',
      'email' => 'Email inválido!',
      'password' => 'Senha inválida!'
    ]);

    if($validate->fails()) {
      return redirect()->back()->withErrors(['credenciais' => 'Credenciais informadas são inválidas.']);
    }

    User::Create($data);
  
    return redirect()->route('auth-login-basic');

  }


}
