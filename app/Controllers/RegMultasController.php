<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RegMultasModel;
use CodeIgniter\HTTP\ResponseInterface;

class RegMultasController extends BaseController
{
    //TRAER DATOS DE MULTAS
    public function getRegMultas()
    {
        $regMultasModel = new RegMultasModel();
        $data = $regMultasModel->getRegMultas();
        return $this->response->setJSON($data);
    }


    // AGREGAR MULTA
    public function addRegMulta()
    {
        $regMultasModel = new RegMultasModel();

        // Intentar leer JSON de forma segura; si falla, usar POST form-data
        try {
            $input = $this->request->getJSON(true) ?: [];
        } catch (\Throwable $e) {
            $input = $this->request->getPost() ?: [];
        }

        $input = is_array($input) ? $input : [];

        // Campos requeridos
        $idusuario = isset($input['idusuario']) ? trim($input['idusuario']) : null;
        $idmulta = isset($input['idmulta']) ? trim($input['idmulta']) : null;
        $fecha = isset($input['fecha']) ? trim($input['fecha']) : null;
        $observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : null;
        $estado = isset($input['estado']) ? trim($input['estado']) : null;

        if (!$idusuario || !$idmulta || !$fecha || !$observaciones || !$estado) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'fecha, observaciones y estado son requeridos'
            ])->setStatusCode(400);
        }

        $data = [
            'idusuario' => $idusuario,
            'idmulta' => $idmulta,
            'fecha' => $fecha,
            'observaciones'    => $observaciones,
            'estado'   => $estado,
        ];

        $insertId = $regMultasModel->insert($data);
        if ($insertId === false) {
            $errors = method_exists($regMultasModel, 'errors') ? $regMultasModel->errors() : null;
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se pudo insertar la multa',
                'errors' => $errors
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'insertId' => $insertId
        ]);
    }


    // Obtener una registro de multa por su ID
    public function getRegMultasById($idregistro)
    {
        $regMultasModel = new RegMultasModel();
        $data = $regMultasModel->getRegMultasById($idregistro);
        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Registro de multa no encontrado'
            ])->setStatusCode(404);
        }
        return $this->response->setJSON($data);
    }


    // Actualizar multa por id (acepta JSON o form-data)
    public function updateRegMulta()
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

        $id = $input['idregistro'] ?? null;
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'idregistro es requerido'
            ])->setStatusCode(400);
        }

        $regmultasModel = new RegMultasModel();
        $existing = $regmultasModel->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Registro de Multa no encontrada'
            ])->setStatusCode(404);
        }

        // Campos a actualizar
        $idusuario = isset($input['idusuario']) ? trim($input['idusuario']) : null;
        $idmulta = isset($input['idmulta']) ? trim($input['idmulta']) : null;
        $fecha = isset($input['fecha']) ? trim($input['fecha']) : null;
        $observaciones = isset($input['observaciones']) ? trim($input['observaciones']) : null;
        $estado = isset($input['estado']) ? trim($input['estado']) : null;

        $data = [];
        if ($idusuario !== null) $data['idusuario'] = $idusuario;
        if ($idmulta !== null) $data['idmulta'] = $idmulta;
        if ($fecha !== null) $data['fecha'] = $fecha;
        if ($observaciones !== null) $data['observaciones'] = $observaciones;
        if ($estado !== null) $data['estado'] = $estado;

        if (empty($data)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No hay campos para actualizar'
            ])->setStatusCode(400);
        }

        // Usar el wrapper del modelo
        $res = $regmultasModel->updateRegMulta($id, $data);
        if ($res === false) {
            $errors = method_exists($regmultasModel, 'errors') ? $regmultasModel->errors() : null;
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se pudo actualizar el registro de multa',
                'errors' => $errors
            ])->setStatusCode(500);
        }

        // Devolver la multa actualizada
        $updated = $regmultasModel->getRegMultasById($id);
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Registro de multa actualizado correctamente',
            'registro' => $updated
        ]);
    }

}
