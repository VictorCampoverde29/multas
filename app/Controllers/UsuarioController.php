<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class UsuarioController extends BaseController
{
    // Obtener lista de usuarios para login (idusuario y descripcion)
    public function getUsuarios()
    {
        $usuarioModel = new UsuarioModel();
        $data = $usuarioModel->getUsuarioLogin();
        return $this->response->setJSON($data);
    }



    public function verificarContrasena()
    {
        // Aceptar JSON o form-data de forma robusta
        // Intentar leer JSON de forma segura; si falla (por ejemplo form-data), usar array vacío
        try {
            $json = $this->request->getJSON(true) ?: [];
        } catch (\Throwable $e) {
            $json = [];
        }

        $post = $this->request->getPost() ?: [];

        $json = is_array($json) ? $json : [];
        $post = is_array($post) ? $post : [];

        // Mezclar: si viene en ambos, JSON tiene preferencia
        $input = array_merge($post, $json);

        // Usar 'descripcion' como identificador de usuario (si no viene, intentar idusuario)
        $identificador = $input['descripcion'] ?? ($input['idusuario'] ?? null);
        $clave = $input['password'] ?? null;

        if (!$identificador || !$clave) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Descripcion y contraseña son requeridos'
            ])->setStatusCode(400);
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->getUser($identificador, $clave);

        if ($usuario) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Correcto el ingreso',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Usuario o contraseña incorrectos'
        ])->setStatusCode(401);
    }




    ///////////////// MANTENIMIENTOS DE USUARIO /////////////////
    // Devuelve todos los usuarios (todos los campos) — para uso en Postman/FlutterFlow
    public function getUsuario()
    {
        $usuarioModel = new UsuarioModel();
        $data = $usuarioModel->getUsuario();
        return $this->response->setJSON($data);
    }

    // Obtener datos de un usuario por su ID
    public function getUsuarioById($id)
    {
        $usuarioModel = new UsuarioModel();
        $data = $usuarioModel->getUsuarioById($id);
        return $this->response->setJSON($data);
    }

    // Actualizar usuario por id (acepta JSON o form-data)
    public function updateUsuario()
    {
        // Leer entrada JSON o form-data de forma segura
        try {
            $json = $this->request->getJSON(true) ?: [];
        } catch (\Throwable $e) {
            $json = [];
        }

        $post = $this->request->getPost() ?: [];
        $json = is_array($json) ? $json : [];
        $post = is_array($post) ? $post : [];
        $input = array_merge($post, $json);

        $id = $input['idusuario'] ?? null;
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'idusuario es requerido'
            ])->setStatusCode(400);
        }

        $usuarioModel = new UsuarioModel();
        $existing = $usuarioModel->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ])->setStatusCode(404);
        }

        // Campos a actualizar
        $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : null;
        $email = isset($input['email']) ? trim(strtolower($input['email'])) : null;
        $password = $input['password'] ?? null; // opcional
        $estado = $input['estado'] ?? null;
        $idperfil = $input['idperfil'] ?? null;

        // Validaciones de unicidad (excluyendo el registro actual)
        if ($descripcion) {
            $other = $usuarioModel->where('descripcion', $descripcion)->where('idusuario !=', $id)->first();
            if ($other) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'La descripcion ya existe'
                ])->setStatusCode(409);
            }
        }

        if ($email) {
            $other = $usuarioModel->where('email', $email)->where('idusuario !=', $id)->first();
            if ($other) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'El correo ya está registrado'
                ])->setStatusCode(409);
            }
        }

        $data = [];
        if ($descripcion !== null) $data['descripcion'] = $descripcion;
        if ($email !== null) $data['email'] = $email;
        if ($estado !== null) $data['estado'] = $estado;
        if ($idperfil !== null) $data['idperfil'] = $idperfil;
        if ($password !== null && $password !== '') {
            // Hashear la nueva contraseña
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if (empty($data)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No hay campos para actualizar'
            ])->setStatusCode(400);
        }

        // Usar el método del modelo para actualizar (encapsula la lógica de datos)
        $res = $usuarioModel->updateUsuario($id, $data);
        if ($res === false) {
            $errors = method_exists($usuarioModel, 'errors') ? $usuarioModel->errors() : null;
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se pudo actualizar el usuario',
                'errors' => $errors
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Usuario actualizado'
        ]);
    }




    //--------------- Agregar nuevo usuario
    public function addUsuario()
    {
        $usuarioModel = new UsuarioModel();

        // Aceptar JSON o form-data
        try {
            $input = $this->request->getJSON(true);
        } catch (\Exception $e) {
            $input = $this->request->getPost();
        }

        // Campos requeridos (normalizar entradas)
        $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : null;
        $password = isset($input['password']) ? $input['password'] : null;
        $email = isset($input['email']) ? trim(strtolower($input['email'])) : null;
        $estado = isset($input['estado']) ? $input['estado'] : null;
        $idperfil = $input['idperfil'] ?? null;

        if (!$descripcion || !$password || !$email || !$estado) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'descripcion, password, email y estado son requeridos'
            ])->setStatusCode(400);
        }

        // Validaciones de unicidad: descripcion y email
        $existsDesc = $usuarioModel->where('descripcion', $descripcion)->first();
        if ($existsDesc) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'La descripcion ya existe'
            ])->setStatusCode(409);
        }

        $existsEmail = $usuarioModel->where('email', $email)->first();
        if ($existsEmail) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El correo ya está registrado'
            ])->setStatusCode(409);
        }

        // Hashear la contraseña antes de guardar
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $data = [
            'descripcion' => $descripcion,
            'password'    => $hashedPassword,
            'email'       => $email,
            'estado'      => $estado,
        ];

        if ($idperfil !== null) {
            $data['idperfil'] = $idperfil;
        }

        $insertId = $usuarioModel->insert($data);

        if ($insertId === false) {
            // intentar leer errores del modelo si existen
            $errors = method_exists($usuarioModel, 'errors') ? $usuarioModel->errors() : null;
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se pudo insertar el usuario',
                'errors' => $errors,
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'insertId' => $insertId
        ]);
    }

    
    // Atención: endpoint sensible — está protegido por el filtro de token del grupo `usuario`.
    public function hashPasswords()
    {
        $usuarioModel = new UsuarioModel();

        $users = $usuarioModel->findAll();
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($users as $user) {
            $id = $user['idusuario'] ?? null;
            $pwd = $user['password'] ?? '';

            if (!$id) {
                continue;
            }

            // Detectar si ya está hasheada: si password_get_info indica algoritmo != 0
            $info = password_get_info($pwd);
            if ($info['algo'] !== 0) {
                $skipped++;
                continue; // ya está hasheada
            }

            // Si la contraseña está vacía o nula, saltar
            if ($pwd === '' || $pwd === null) {
                $skipped++;
                continue;
            }

            // Re-hashear y actualizar
            $newHash = password_hash($pwd, PASSWORD_DEFAULT);
            try {
                $res = $usuarioModel->update($id, ['password' => $newHash]);
                if ($res) {
                    $updated++;
                } else {
                    $errors[] = $id;
                }
            } catch (\Throwable $e) {
                $errors[] = ['id' => $id, 'error' => $e->getMessage()];
            }
        }

        return $this->response->setJSON([
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors
        ]);
    }

    // Hashear una sola contraseña enviada por POST o JSON y devolver el hash
    public function HasheoContra()
    {
        // Intentar leer JSON primero de forma segura
        try {
            $json = $this->request->getJSON(true) ?: [];
        } catch (\Throwable $e) {
            $json = [];
        }

        $post = $this->request->getPost() ?: [];
        $json = is_array($json) ? $json : [];
        $post = is_array($post) ? $post : [];

        // Merge: JSON tiene preferencia
        $input = array_merge($post, $json);

        $clave = $input['password'] ?? null;

        if (!$clave) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'password es requerido'
            ])->setStatusCode(400);
        }

        $hashedPassword = password_hash($clave, PASSWORD_DEFAULT);

        return $this->response->setJSON([
            'status' => 'success',
            'password_hash' => $hashedPassword
        ]);
    }

}
