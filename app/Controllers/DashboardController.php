<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DashboardModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function getStats()
    {
        $model = new DashboardModel();
        $data = $model->getStats();
        return $this->response->setJSON(['data' => $data]);
        
    }
    public function getUltimasMultas()
    {
        $model = new DashboardModel();
        $data = $model->getUltimasMultas();
        return $this->response->setJSON(['data' => $data]);
    }

}