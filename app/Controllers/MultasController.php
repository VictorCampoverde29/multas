<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MultasModel;
use CodeIgniter\HTTP\ResponseInterface;

class MultasController extends BaseController
{

    //TRAER DATOS DE MULTAS
    public function getMultas()
    {
        $multasModel = new MultasModel();
        $data = $multasModel->getMultas();
        return $this->response->setJSON($data);
    }

    // AGREGAR MULTA
    public function addMulta()
    {
        $multasModel = new MultasModel();

        // Intentar leer JSON de forma segura; si falla, usar POST form-data
        try {
            $input = $this->request->getJSON(true) ?: [];
        } catch (\Throwable $e) {
            $input = $this->request->getPost() ?: [];
        }

        $input = is_array($input) ? $input : [];

        // Campos requeridos
        $concepto = isset($input['concepto']) ? trim($input['concepto']) : null;
        $monto = isset($input['monto']) ? $input['monto'] : null;
        $estado = isset($input['estado']) ? trim($input['estado']) : null;

        if (!$concepto || $monto === null || $monto === '' || !$estado) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'concepto, monto y estado son requeridos'
            ])->setStatusCode(400);
        }

        if (!is_numeric($monto)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'monto debe ser numérico'
            ])->setStatusCode(400);
        }

        $data = [
            'concepto' => $concepto,
            'monto'    => $monto,
            'estado'   => $estado,
        ];

        $insertId = $multasModel->insert($data);
        if ($insertId === false) {
            $errors = method_exists($multasModel, 'errors') ? $multasModel->errors() : null;
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

    // Obtener una multa por su ID
    public function getMultasById($id)
    {
        $multasModel = new MultasModel();
        $data = $multasModel->getMultasById($id);
        if (!$data) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Multa no encontrada'
            ])->setStatusCode(404);
        }
        return $this->response->setJSON($data);
    }

    // Actualizar multa por id (acepta JSON o form-data)
    public function updateMulta()
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

        $id = $input['idmulta'] ?? null;
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'idmulta es requerido'
            ])->setStatusCode(400);
        }

        $multasModel = new MultasModel();
        $existing = $multasModel->find($id);
        if (!$existing) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Multa no encontrada'
            ])->setStatusCode(404);
        }

        // Campos a actualizar
        $concepto = isset($input['concepto']) ? trim($input['concepto']) : null;
        $monto = $input['monto'] ?? null;
        $estado = isset($input['estado']) ? trim($input['estado']) : null;

        $data = [];
        if ($concepto !== null) $data['concepto'] = $concepto;
        if ($monto !== null && $monto !== '') {
            if (!is_numeric($monto)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'monto debe ser numérico'
                ])->setStatusCode(400);
            }
            $data['monto'] = $monto;
        }
        if ($estado !== null) $data['estado'] = $estado;

        if (empty($data)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No hay campos para actualizar'
            ])->setStatusCode(400);
        }

        // Usar el wrapper del modelo
        $res = $multasModel->updateMulta($id, $data);
        if ($res === false) {
            $errors = method_exists($multasModel, 'errors') ? $multasModel->errors() : null;
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No se pudo actualizar la multa',
                'errors' => $errors
            ])->setStatusCode(500);
        }

        // Devolver la multa actualizada
        $updated = $multasModel->getMultasById($id);
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Multa actualizada',
            'multa' => $updated
        ]);
    }


}
