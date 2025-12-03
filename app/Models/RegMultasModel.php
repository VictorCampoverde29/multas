<?php

namespace App\Models;

use CodeIgniter\Model;

class RegMultasModel extends Model
{
    protected $table            = 'registro_multa';
    protected $primaryKey       = 'idregistro';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'idregistro',
        'idusuario',
        'idmulta',
        'fecha',
        'observaciones',
        'estado'
    ];

    //Obtiene todos los usuarios
    public function getRegMultas()
    {
        return $this->findAll(); //trae todos los datos del usuario
    }

    // Obtener multa por id
    public function getRegMultasById($idregistro)
    {
        return $this->where('idregistro', $idregistro)->first();
    }

    // Actualizar multa por id
    public function updateRegMulta($id, $data)
    {
        return $this->update($id, $data);
    }
}
