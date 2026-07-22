<?php

/**
 * @file    RememberTokenService.php
 * @package App\Services\Auth
 *
 * Lógica de negocio para los tokens persistentes "Recuérdame".
 *
 * Principio de seguridad:
 *  - El cliente recibe el token crudo (64 hex chars, 32 bytes de entropía).
 *  - La base de datos almacena únicamente su hash SHA-256.
 *  - Incluso con acceso a la tabla, un atacante no puede recuperar el token
 *    ni usarlo para impersonar a un usuario.
 */

namespace App\Services\Auth;

use CodeIgniter\Database\ConnectionInterface;

/**
 * Gestiona el ciclo de vida de los tokens "Recuérdame":
 * creación, validación e invalidación.
 *
 * Tabla: `remember_tokens` (id, id_usuario, token_hash, expires_at, created_at).
 */
class RememberTokenService
{
    /** Duración del token en segundos (30 días). */
    private const TTL = 2_592_000;

    private ConnectionInterface $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // OPERACIONES PRINCIPALES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Genera un nuevo token para el usuario, lo persiste hasheado
     * y devuelve el valor crudo para enviarlo en la cookie del cliente.
     *
     * @param  int    $idUsuario
     * @return string Token crudo (64 hex chars). Enviar directamente a la cookie.
     */
    public function crear(int $idUsuario): string
    {
        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);

        $this->db->table('remember_tokens')->insert([
            'id_usuario' => $idUsuario,
            'token_hash' => $tokenHash,
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $rawToken;
    }

    /**
     * Elimina un token concreto (logout del dispositivo actual).
     *
     * @param string $rawToken Token crudo leído de la cookie del cliente.
     */
    public function invalidar(string $rawToken): void
    {
        $this->db->table('remember_tokens')
            ->where('token_hash', hash('sha256', $rawToken))
            ->delete();
    }

    /**
     * Valida el token crudo y devuelve los datos del usuario si:
     *  - El hash existe en la tabla.
     *  - El token no ha expirado.
     *  - El usuario está ACTIVO.
     *
     * Devuelve null en cualquier otro caso.
     *
     * @param  string                    $rawToken Token crudo leído de la cookie.
     * @return array<string, mixed>|null           Fila con id_usuario, nombre_user,
     *                                             nombres, apellidos, id_rol, rol.
     */
    public function validarYObtenerUsuario(string $rawToken): ?array
    {
        $row = $this->db->query(
            'SELECT u.id_usuario, u.nombre_user, u.estado, u.id_rol,
                    p.nombres, p.apellidos, r.rol
             FROM   remember_tokens rt
             JOIN   usuarios u ON u.id_usuario  = rt.id_usuario
             JOIN   personas p ON p.id_persona  = u.id_persona
             JOIN   roles    r ON r.id_rol      = u.id_rol
             WHERE  rt.token_hash = ?
               AND  rt.expires_at > ?',
            [hash('sha256', $rawToken), date('Y-m-d H:i:s')]
        )->getRowArray();

        if (!$row || $row['estado'] === 'INACTIVO') {
            return null;
        }

        return $row;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS ESTÁTICOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Escribe los datos del usuario en la sesión activa.
     * Regenera el ID de sesión para prevenir session fixation.
     *
     * Válido tanto para login normal como para auto-login por cookie.
     *
     * @param array<string, mixed> $usuario Resultado de validarYObtenerUsuario()
     *                                      o UsuariosModel::findByUsername().
     */
    public static function iniciarSesion(array $usuario): void
    {
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
    }

    /** Devuelve la duración del token en segundos (para configurar la cookie). */
    public static function ttl(): int
    {
        return self::TTL;
    }
}
