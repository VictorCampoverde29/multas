<?php

namespace App\Models;

use CodeIgniter\Model;

class DashboardModel extends Model
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

    public function getStats()
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

    public function getUltimasMultas()
    {
        return $this->db->table('registro_multa r')
            ->select('
                r.idregistro,
                r.fecha,
                r.observaciones,
                r.estado,
                u.descripcion AS usuario_descripcion,
                m.concepto AS multa_concepto,
                m.monto,
                UPPER(CONCAT(SUBSTRING(u.descripcion, 1, 1), SUBSTRING(u.descripcion, 4, 1))) AS usuario_abreviatura
            ')
            ->join('usuario u', 'u.idusuario = r.idusuario')
            ->join('multas m', 'm.idmulta = r.idmulta')
            ->orderBy('r.fecha', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();
    }


}
