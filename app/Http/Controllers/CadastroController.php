<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CadastroController extends Controller
{
    public function create(Request $request)
    {
       

        // Criar um novo usuário com os dados recebidos
         User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Criptografar a senha
            'identificador' => $request->identificador,
        ]);

        // Redirecionar para a página desejada após a criação do usuário
        return redirect()->route('/dashboard');
    }
}
