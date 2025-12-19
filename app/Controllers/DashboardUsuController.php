<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardUsuModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardUsuController extends BaseController
{
    
    public function getUserDashboard()
{
    // SOLO recibir idusuario desde la API
    $idusuario = $this->request->getGet('idusuario');
    
    $model = new DashboardUsuModel();

    $datosUsuario = $model->getUserDashboardData($idusuario);

    if (!$datosUsuario) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Usuario no existe'
        ])->setStatusCode(404);
    }

    $data = [
        'success' => true,
        'usuario' => $datosUsuario,
        'cantidad_multas' => $model->getMultasCount($idusuario)['cantidad_multas'] ?? 0,
        'cantidad_multas_anuladas' => $model->getMultasCountAnuladas($idusuario)['cantidad_multas_anuladas'] ?? 0,
        'total_dinero_descontado' => $model->getTotalDineroDescontado($idusuario)['total_dinero'] ?? 0,
        'ultimas_multas' => $model->getUltimasMultasUsuario($idusuario, 3)
    ];

    return $this->response->setJSON($data);
}



}
