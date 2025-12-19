<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuario';
    protected $primaryKey       = 'idusuario';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'idusuario',
        'idperfil',
        'descripcion',
        'password',
        'email',
        'f_registro',
        'estado'
    ];

    ///////////////////
    //Obtiene usuarios activos para login
    public function getUsuarioLogin()
    {
        return $this->select('idusuario, descripcion')
            ->where('estado', 'ACTIVO')
            ->findAll();
    }


    // Validar usuario y contraseña
    // $usuarioIdentificador puede ser id (numérico) o 'descripcion' (string)
    public function getUser($usuarioIdentificador, $clave)
    {
        $builder = $this->select('idusuario, descripcion, idperfil, password, email, estado')
            ->where('estado', 'ACTIVO');

        if (is_numeric($usuarioIdentificador)) {
            $builder->where('idusuario', $usuarioIdentificador);
        } else {
            $builder->where('descripcion', $usuarioIdentificador);
        }

        $user = $builder->first();

        // Verificar contraseña usando password_verify
        if ($user && isset($user['password']) && password_verify($clave, $user['password'])) {
            // No devolver la contraseña al controlador
            unset($user['password']);
            return $user; // Usuario y contraseña correctos
        }

        return null; // Usuario inactivo o contraseña incorrecta
    }
    ///////////////////


    //DATOS DEL USUARIO: trae todos los usuarios menos el admin, join con perfil y cantidad de multas.
    public function getUsuario()
    {
        return $this->select("usuario.idusuario, usuario.idperfil, usuario.descripcion, usuario.email,
         usuario.f_registro, usuario.estado, perfil.descripcion as perfil_descripcion,
         (SELECT COUNT(*) FROM registro_multa r WHERE r.idusuario = usuario.idusuario) AS cantidad_multas,
         UPPER(CONCAT(SUBSTRING(usuario.descripcion, 1, 1), SUBSTRING(usuario.descripcion, 4, 1))) AS abreviatura")
            ->join('perfil', 'perfil.idperfil = usuario.idperfil')
            ->where('usuario.idusuario !=', 1)
            ->findAll();
    }

    //OBTENER LOS DATOS POR ID PARA EDITAR
    public function getUsuarioById($idusuario)
    {
        return $this->select("usuario.*,
         UPPER(CONCAT(SUBSTRING(usuario.descripcion, 1, 1), SUBSTRING(usuario.descripcion, 4, 1))) AS abreviatura")
            ->where('idusuario', $idusuario)
            ->first();
    }


    //ACTUALIZAR LOS DATOS AL HABER EDITADO
    public function updateUsuario($id, $data)
    {
        return $this->update($id, $data);
    }
}
