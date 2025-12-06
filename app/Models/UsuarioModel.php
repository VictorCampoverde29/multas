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
        'estado'
    ];

    // //Obtiene todos los usuarios
    // public function getUsuario()
    // {
    //     return $this->select('idusuario,idperfil, descripcion, email, estado')
    //         ->findAll(); //trae todos los datos del usuario
    // }

    //Obtiene todos los usuarios con la descripción del perfil
    public function getUsuario()
    {
        return $this->select('usuario.idusuario, usuario.idperfil, usuario.descripcion, usuario.email, usuario.estado, perfil.descripcion as perfil_descripcion')
            ->join('perfil', 'perfil.idperfil = usuario.idperfil')
            ->findAll();
    }

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
        $builder = $this->select('idusuario, descripcion, idperfil, password')
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






    //FUNCION PARA OBTENER LOS DATOS POR ID PARA EDITAR
    public function getUsuarioById($idusuario)
    {
        return $this->where('idusuario', $idusuario)
            ->first();
    }


    // FUNCION PARA ACTUALIZAR LOS DATOS AL HABER EDITADO
    public function updateUsuario($id, $data)
    {
        return $this->update($id, $data);
    }
}
