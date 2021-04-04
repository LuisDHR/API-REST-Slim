<?php
namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HomeAction
{
    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        $result = ['error' => ['message' => 'Validation failed']];
        // $succes = ['success' => true];

        $response->getBody()->write(json_encode($result));

        // return $response->withHeader('Content-Type', 'application/json');
        return $response->withHeader('Content-Type', 'application/json');
    }
}