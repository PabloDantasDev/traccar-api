<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }


 public function registra(Request $request)
  {
    
  User::Create([
    'identificador' => $request->identificador,
    'email' => $request->email,
    'password' => $request->password,

  ]);
 
  return redirect()->route('auth-login-basic');

  }


}
