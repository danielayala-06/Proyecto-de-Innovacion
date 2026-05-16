<?php

/**
 * @file    AuthController.php
 * @package App\Controllers
 *
 * Controlador de autenticación: inicio y cierre de sesión.
 *
 * Protecciones implementadas:
 *  - Fuerza bruta   : rate limiting por IP (10 intentos / 15 min)
 *                     y por nombre de usuario (5 intentos / 15 min).
 *  - SQL Injection  : Query Builder de CI4 usa consultas parametrizadas.
 *  - XSS            : esc() en vistas + CSRF global en Filters.php.
 *  - Session fixation: session()->regenerate(true) antes de escribir sesión.
 *  - Enumeración    : mensaje genérico cuando usuario o contraseña fallan.
 *  - Caracteres     : regex que rechaza emojis y caracteres fuera de ASCII imprimible.
 */

namespace App\Controllers;

use App\Models\UsuariosModel;

/**
 * Gestiona el ciclo de vida de la autenticación web.
 *
 * Rutas:
 *  - GET  /login  → login()
 *  - POST /login  → authenticate()
 *  - GET  /logout → logout()
 *
 * Las claves de throttler se derivan del hash MD5 de IP / nombre de usuario
 * para evitar inyección de caracteres especiales en la clave de caché.
 */
class AuthController extends BaseController
{
    /** @var int Intentos máximos por dirección IP en la ventana de bloqueo. */
    private const MAX_IP_ATTEMPTS   = 10;

    /** @var int Intentos máximos por nombre de usuario en la ventana de bloqueo. */
    private const MAX_USER_ATTEMPTS = 5;

    /** @var int Duración del bloqueo en segundos (15 minutos). */
    private const LOCKOUT_SECONDS   = 900;

    /**
     * Carga los helpers necesarios para el controlador.
     */
    public function __construct()
    {
        helper(['url', 'form']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RUTAS WEB
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Muestra el formulario de inicio de sesión.
     *
     * Si el usuario ya tiene sesión activa es redirigido al dashboard.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|string Vista o redirección.
     */
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/');
        }

        return view('auth/login');
    }

    /**
     * Procesa las credenciales enviadas por el formulario de login.
     *
     * Flujo:
     *  1. Rate limiting por IP (fuerza bruta de red).
     *  2. Validación de formato con regex estrictos.
     *  3. Rate limiting por nombre de usuario (credential stuffing).
     *  4. Búsqueda del usuario y verificación de contraseña con bcrypt.
     *  5. Comprobación de estado de la cuenta.
     *  6. Regeneración de sesión y escritura de datos de autenticación.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse Redirección al dashboard o de vuelta al login con error.
     */
    public function authenticate()
    {
        $throttler = service('throttler');
        $ip        = $this->request->getIPAddress();

        // ── 1. Rate limiting por IP ───────────────────────────────────────────
        if (!$throttler->check('login_ip_' . md5($ip), self::MAX_IP_ATTEMPTS, self::LOCKOUT_SECONDS)) {
            return redirect()->back()
                ->with('error', 'Demasiados intentos desde tu dirección. Espera 15 minutos e inténtalo de nuevo.');
        }

        // ── 2. Validación de formato ──────────────────────────────────────────
        // nombre_user: alfanumérico + . _ - (sin emojis).
        // password   : solo caracteres ASCII imprimibles (0x20–0x7E).
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

        // ── 4. Verificación de credenciales ───────────────────────────────────
        $model   = new UsuariosModel();
        $usuario = $model->findByUsername($nombreUser);

        // Mensaje genérico: no revelar si el usuario existe o no (anti-enumeración).
        $errorGenerico = 'Usuario o contraseña incorrectos.';

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', $errorGenerico);
        }

        // ── 5. Estado de la cuenta ────────────────────────────────────────────
        if ($usuario['estado'] === 'INACTIVO') {
            return redirect()->back()
                ->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        }

        // ── 6. Sesión segura ──────────────────────────────────────────────────
        // regenerate(true) destruye la sesión anterior y crea un nuevo ID.
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

    /**
     * Cierra la sesión activa y redirige al login.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')
            ->with('info', 'Sesión cerrada correctamente.');
    }
}
