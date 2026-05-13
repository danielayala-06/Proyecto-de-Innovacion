<?php

namespace App\Controllers;

use App\Models\UsuariosModel;

/**
 * AuthController — gestiona inicio y cierre de sesión.
 *
 * Protecciones implementadas:
 *  - Fuerza bruta : rate limiting por IP (10 intentos / 15 min) y por usuario (5 intentos / 15 min)
 *  - SQL Injection: Query Builder de CI4 usa consultas parametrizadas
 *  - XSS          : esc() en vistas + CSRF habilitado en Filters
 *  - Fijación de sesión: session()->regenerate() al autenticar
 *  - Enumeración  : mismo mensaje de error para "usuario no existe" y "contraseña incorrecta"
 *  - Caracteres   : regex backend que rechaza emojis y caracteres fuera del rango ASCII imprimible
 */
class AuthController extends BaseController
{
    // Intentos máximos por IP antes de bloqueo
    private const MAX_IP_ATTEMPTS   = 10;
    // Intentos máximos por nombre de usuario antes de bloqueo
    private const MAX_USER_ATTEMPTS = 5;
    // Ventana de bloqueo en segundos (15 minutos)
    private const LOCKOUT_SECONDS   = 900;

    public function __construct()
    {
        helper(['url', 'form']);
    }

    // ── GET /login ────────────────────────────────────────────────────────────
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        return view('auth/login');
    }

    // ── POST /login ───────────────────────────────────────────────────────────
    public function authenticate()
    {
        $throttler = service('throttler');
        $ip        = $this->request->getIPAddress();

        // ── 1. Rate limiting por IP ───────────────────────────────────────────
        if (!$throttler->check('login_ip_' . md5($ip), self::MAX_IP_ATTEMPTS, self::LOCKOUT_SECONDS)) {
            return redirect()->back()
                ->with('error', 'Demasiados intentos desde tu dirección. Espera 15 minutos e inténtalo de nuevo.');
        }

        // ── 2. Validación backend ─────────────────────────────────────────────
        // Regex: solo caracteres ASCII imprimibles (0x20–0x7E), sin emojis ni
        // caracteres multibyte que podrían evadir filtros.
        $rules = [
            'nombre_user' => [
                'label'  => 'Usuario',
                'rules'  => 'required|min_length[4]|max_length[50]|regex_match[/^[a-zA-Z0-9._\-]+$/]',
                'errors' => [
                    'required'    => 'El usuario es obligatorio.',
                    'min_length'  => 'El usuario debe tener mínimo 4 caracteres.',
                    'max_length'  => 'El usuario no puede superar 50 caracteres.',
                    'regex_match' => 'El usuario solo acepta letras, números, puntos, guiones y guiones bajos.',
                ],
            ],
            'password' => [
                'label'  => 'Contraseña',
                'rules'  => 'required|min_length[8]|max_length[100]|regex_match[/^[\x20-\x7E]+$/]',
                'errors' => [
                    'required'    => 'La contraseña es obligatoria.',
                    'min_length'  => 'La contraseña debe tener mínimo 8 caracteres.',
                    'max_length'  => 'La contraseña no puede superar 100 caracteres.',
                    'regex_match' => 'La contraseña contiene caracteres no permitidos (emojis o caracteres especiales).',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $nombreUser = $this->request->getPost('nombre_user');
        $password   = $this->request->getPost('password');

        // ── 3. Rate limiting por nombre de usuario ────────────────────────────
        if (!$throttler->check('login_user_' . md5($nombreUser), self::MAX_USER_ATTEMPTS, self::LOCKOUT_SECONDS)) {
            return redirect()->back()
                ->with('error', 'Esta cuenta ha sido bloqueada temporalmente. Espera 15 minutos e inténtalo de nuevo.');
        }

        // ── 4. Búsqueda de usuario ────────────────────────────────────────────
        $model   = new UsuariosModel();
        $usuario = $model->findByUsername($nombreUser);

        // Mensaje genérico: no revelar si el usuario existe o no (evita enumeración)
        $errorGenerico = 'Usuario o contraseña incorrectos.';

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', $errorGenerico);
        }

        // ── 5. Verificar estado de la cuenta ──────────────────────────────────
        if ($usuario['estado'] === 'INACTIVO') {
            return redirect()->back()
                ->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        }

        // ── 6. Autenticación exitosa ──────────────────────────────────────────
        // Regenerar ID de sesión para prevenir session fixation
        session()->regenerate(true);
        session()->set([
            'logged_in'   => true,
            'usuario_id'  => (int) $usuario['id_usuario'],
            'nombre_user' => $usuario['nombre_user'],
            'nombres'     => $usuario['nombres'],
            'apellidos'   => $usuario['apellidos'],
            'id_rol'      => (int) $usuario['id_rol'],
            'rol'         => $usuario['rol'],
        ]);

        return redirect()->to('/');
    }

    // ── GET /logout ───────────────────────────────────────────────────────────
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')
            ->with('info', 'Sesión cerrada correctamente.');
    }
}
