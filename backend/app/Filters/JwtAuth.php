<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorized();
        }

        $token = substr($authHeader, 7);

        try {
            $key = 'baovola-secret-key-12345678901234567890123456';
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            $request->user = $decoded;
        } catch (\Exception $e) {
            return $this->unauthorized();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function unauthorized()
    {
        $response = service('response');
        $response->setStatusCode(401);
        $response->setJSON([
            'status' => false,
            'message' => 'Unauthorized: Invalid or missing token'
        ]);
        return $response;
    }
}