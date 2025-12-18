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

    //Obtiene todos los registros de multas con información de usuario y multa
    public function getRegMultas()
    {
        return $this->select('registro_multa.idregistro, registro_multa.idusuario, 
                              registro_multa.idmulta, registro_multa.fecha, registro_multa.observaciones, 
                              registro_multa.estado, usuario.descripcion as usuario_descripcion, 
                              multas.concepto as multa_concepto, multas.monto,
                              UPPER(CONCAT(SUBSTRING(usuario.descripcion, 1, 1), SUBSTRING(usuario.descripcion, 4, 1))) AS usuario_abreviatura')
            ->join('usuario', 'usuario.idusuario = registro_multa.idusuario')
            ->join('multas', 'multas.idmulta = registro_multa.idmulta')
            ->findAll();
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
