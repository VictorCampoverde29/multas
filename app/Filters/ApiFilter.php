<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services; // <— IMPORTANTE

class ApiFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader    = $request->getHeaderLine('Authorization');
        $expectedToken = env('API_TOKEN');

        // si tu PHP < 8, cambia str_starts_with por strpos === 0
        if ($authHeader === '' || strpos($authHeader, 'Bearer ') !== 0) {
            return Services::response()
                ->setJSON(['error' => 'Token requerido'])
                ->setStatusCode(401);
        }

        $token = substr($authHeader, 7);
        if ($token !== $expectedToken) {
            return Services::response()
                ->setJSON(['error' => 'Token inválido'])
                ->setStatusCode(403);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
