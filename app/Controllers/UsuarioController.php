<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class UsuarioController extends BaseController
{

    // Hashear una sola contraseña 
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


    // Obtener lista de usuarios para login
    public function getUsuarios()
    {
        $usuarioModel = new UsuarioModel();
        $data = $usuarioModel->getUsuarioLogin();
        return $this->response->setJSON($data);
    }

    
    // Verificar usuario y contraseña
    public function verificarContrasena()
    {
        // Aceptar JSON o form-data de forma robusta
        
        try {
            $json = $this->request->getJSON(true) ?: [];
        } catch (\Throwable $e) {
            $json = [];
        }

        $post = $this->request->getPost() ?: [];

        $json = is_array($json) ? $json : [];
        $post = is_array($post) ? $post : [];

        
        $input = array_merge($post, $json);

        // Identificador puede ser 'descripcion' o 'idusuario'
        $identificador = $input['descripcion'] ?? ($input['idusuario'] ?? null);
        $clave = $input['password'] ?? null;

        if ($identificador === null || $clave === null || $clave === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Debe enviar idusuario o descripcion, y password'
            ])->setStatusCode(400);
        }

        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->getUser($identificador, $clave);

        if ($usuario) {
            // Calcular abreviatura (primera y cuarta letra del nombre)
            $desc = isset($usuario['descripcion']) ? strtoupper($usuario['descripcion']) : '';
            $primera = $desc !== '' ? substr($desc, 0, 1) : '';
            $cuarta = strlen($desc) >= 4 ? substr($desc, 3, 1) : (strlen($desc) >= 2 ? substr($desc, 1, 1) : '');
            $abreviatura = $primera . $cuarta;

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Correcto el ingreso',
                'data' => [
                    'idusuario'   => $usuario['idusuario'],
                    'descripcion' => $usuario['descripcion'],
                    'idperfil'    => $usuario['idperfil'],
                    'email'       => $usuario['email'] ?? null,
                    'estado'      => $usuario['estado'] ?? null,
                    'abreviatura' => $abreviatura,
                ]
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Usuario o contraseña incorrectos'
        ])->setStatusCode(401);
    }




    ///////////////// MANTENIMIENTOS DE USUARIO /////////////////
    // Devuelve todos los usuarios menos el admin
    public function getUsuario()
    {
        $usuarioModel = new UsuarioModel();
        $data = $usuarioModel->getUsuario();
        return $this->response->setJSON(['data' => $data]);
    }

    // Obtener datos de un usuario por su ID
    public function getUsuarioById($id)
    {
        $usuarioModel = new UsuarioModel();
        $data = $usuarioModel->getUsuarioById($id);
        return $this->response->setJSON($data);
    }

    // Actualizar usuario por id
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

        // Instanciar modelo para posibles resoluciones alternativas del ID
        $usuarioModel = new UsuarioModel();

        // Resolver idusuario desde varias fuentes: body, query (?idusuario=) o por descripcion
        $id = $input['idusuario'] ?? ($this->request->getGet('idusuario') ?? null);
        if (!$id && isset($input['descripcion'])) {
            // Si no vino idusuario pero vino descripcion, intentar resolver el ID
            $byDesc = $usuarioModel->where('descripcion', trim($input['descripcion']))->first();
            if ($byDesc && isset($byDesc['idusuario'])) {
                $id = $byDesc['idusuario'];
            }
        }

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'idusuario es requerido (puedes enviarlo en el body, en la query ?idusuario=, o pasando descripcion)'
            ])->setStatusCode(400);
        }
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
        $idperfilRaw = $input['idperfil'] ?? null; // llega como string "1"/"2" desde FlutterFlow
        $idperfil = is_numeric($idperfilRaw) ? (int) $idperfilRaw : null;

        // Validaciones (solo si el valor cambió respecto al existente)
        if ($descripcion && $descripcion !== ($existing['descripcion'] ?? null)) {
            $other = $usuarioModel->where('descripcion', $descripcion)->where('idusuario !=', $id)->first();
            if ($other) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'La descripcion ya existe'
                ])->setStatusCode(409);
            }
        }

        if ($email && $email !== ($existing['email'] ?? null)) {
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

        // Campos requeridos
        $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : null;
        $password = isset($input['password']) ? $input['password'] : null;
        $email = isset($input['email']) ? trim(strtolower($input['email'])) : null;
        $estado = isset($input['estado']) ? $input['estado'] : null;
        $idperfil = $input['idperfil'] ?? null;

        if (!$descripcion || !$email || !$estado) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'descripcion, email y estado son requeridos'
            ])->setStatusCode(400);
        }

        // Validaciones
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

        // Hashear la contraseña solo si viene proporcionada
        $hashedPassword = ($password !== null && $password !== '') ? password_hash($password, PASSWORD_DEFAULT) : null;

        $data = [
            'descripcion' => $descripcion,
            'email'       => $email,
            'estado'      => $estado,
            'f_registro'  => date('Y-m-d H:i:s'),
        ];

        if ($hashedPassword !== null) {
            $data['password'] = $hashedPassword;
        }

        // Convertir idperfil (string) a entero si viene
        if ($idperfil !== null) {
            $data['idperfil'] = is_numeric($idperfil) ? (int) $idperfil : null;
        }

        $insertId = $usuarioModel->insert($data);

        if ($insertId === false) {
            $errors = method_exists($usuarioModel, 'errors') ? $usuarioModel->errors() : null;
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se pudo insertar el usuario',
                'errors' => $errors,
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'mensaje' => 'Usuario agregado',
            'insertId' => $insertId
        ]);
    }
}
