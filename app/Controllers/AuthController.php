<?php

namespace App\Controllers;

use App\Models\Usuario;

class AuthController extends BaseController
{
    public function index()
    {
        if (session()->get('usuario')) {
            return redirect()->to(base_url('/'));
        }
        return view('login/login');
    }

    public function login()
    {
        $rules = [
            'nombre_user' => 'required|min_length[3]|max_length[50]',
            'password'    => 'required|min_length[6]',
        ];

        $messages = [
            'nombre_user' => [
                'required'   => 'El nombre de usuario es obligatorio.',
                'min_length' => 'El usuario debe tener al menos 3 caracteres.',
            ],
            'password'    => [
                'required'   => 'La contraseña es obligatoria.',
                'min_length' => 'La contraseña debe tener al menos 6 caracteres.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $nombreUser = $this->request->getPost('nombre_user');
        $password   = $this->request->getPost('password');

        $model   = new Usuario();
        $usuario = $model->findByUsername($nombreUser);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Usuario o contraseña incorrectos.');
        }

        session()->set('usuario', [
            'id'          => $usuario['id_usuario'],
            'nombre_user' => $usuario['nombre_user'],
            'tipo'        => $usuario['tipo_usuario'],
            'id_rol'      => $usuario['id_rol'],
        ]);

        return redirect()->to(base_url('/'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/login'));
    }
}
