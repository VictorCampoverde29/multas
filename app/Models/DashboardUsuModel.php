<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardUsuModel extends Model
{
    protected $table            = 'usuario';
    protected $primaryKey       = 'id';
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
        'f_registro',
        'estado'
    ];

    public function getDashboardData()
    {
        return $this->select("
                (SELECT COUNT(*) FROM usuario) AS total_usuarios,
                (SELECT COUNT(*) FROM registro_multa) AS multas_generadas,
                (SELECT COUNT(*) FROM registro_multa WHERE estado = 'ANULADO') AS multas_anuladas,
                (SELECT COALESCE(SUM(m.monto), 0) 
                 FROM registro_multa r 
                 JOIN multas m ON m.idmulta = r.idmulta 
                 WHERE r.estado != 'ANULADO') AS total_dinero
            ")
            ->first();
    }
}
