<?php

namespace App\Models;

use CodeIgniter\Model;

class MultasModel extends Model
{
    protected $table            = 'multas';
    protected $primaryKey       = 'idmulta';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'idmulta',
        'concepto',
        'monto',
        'estado'
    ];


    //Obtiene todos los usuarios
    public function getMultas()
    {
        return $this->findAll(); //trae todos los datos del usuario
    }

    // Obtener multa por id
    public function getMultasById($idmulta)
    {
        return $this->where('idmulta', $idmulta)->first();
    }

    // Actualizar multa por id
    public function updateMulta($id, $data)
    {
        return $this->update($id, $data);
    }
}
