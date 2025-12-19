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



    public function getUserDashboardData($idusuario)
    {
        return $this->select('idusuario, descripcion, email, idperfil, f_registro, estado')
            ->where('idusuario', $idusuario)
            ->where('idperfil', 2)
            ->first();
    }



    public function getMultasCount($idusuario)
    {
        return $this->db->table('registro_multa')
            ->selectCount('idregistro', 'cantidad_multas')
            ->where('idusuario', $idusuario)
            ->get()
            ->getRowArray();
    }


    public function getMultasCountAnuladas($idusuario)
    {
        return $this->db->table('registro_multa')
            ->selectCount('idregistro', 'cantidad_multas_anuladas')
            ->where('idusuario', $idusuario)
            ->where('estado', 'ANULADO')
            ->get()
            ->getRowArray();
    }



    public function getTotalDineroDescontado($idusuario)
    {
        return $this->db->table('registro_multa r')
            ->select('COALESCE(SUM(m.monto), 0) AS total_dinero')
            ->join('multas m', 'm.idmulta = r.idmulta')
            ->where('r.idusuario', $idusuario)
            ->groupStart()
                ->where('r.estado !=', 'ANULADO')
                ->orWhere('r.estado IS NULL', null, false)
            ->groupEnd()
            ->get()
            ->getRowArray();
    }



    public function getUltimasMultasUsuario($idusuario, $limit = 3)
    {
        return $this->db->table('registro_multa r')
            ->select('
                r.idregistro,
                r.fecha,
                r.observaciones,
                r.estado,
                m.concepto AS multa_concepto,
                m.monto
            ')
            ->join('multas m', 'm.idmulta = r.idmulta')
            ->where('r.idusuario', $idusuario)
            ->where('r.estado !=', 'ANULADO')
            ->orderBy('r.fecha', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
