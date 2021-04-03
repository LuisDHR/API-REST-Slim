<?php
namespace App\Action;

use App\Database\Firebase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class getProd
{
    private $database;

    public function __construct()
    {
        $this->database = new Firebase();
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response,
        $args
    ): ResponseInterface {
        $user = $args['user'];
        $pass = $args['pass'];
        $category = $args['categoria'];

        $categoria = strtolower($category);
        $resp = array(
            'code' => 999,
            'message' => $this->database->read_collection('respuestas', 999),
            'data' => '',
            'status' => 'error'
        );

        $res_usuario = $this->database->read_collection('usuarios', $user);
        if ( !is_null($res_usuario) ) {
            if ( $res_usuario === md5($pass) ) {
                $res_productos = $this->database->read_collection('productos', $categoria);
                if ( !is_null($res_productos) ) {
                    $resp['code'] = 200;
                    $resp['message'] = $this->database->read_collection('respuestas', 200);
                    $resp['status'] = 'success';
                    $resp['data'] = $res_productos;
                }
                else {
                    $resp['code'] = 300;
                    $resp['message'] = $this->database->read_collection('respuestas', 300);
                }
            }
            else {
                $resp['code'] = 501;
                $resp['message'] = $this->database->read_collection('respuestas', 501);;
            }
        }
        else {
            $resp['code'] = 500;
            $resp['message'] = $this->database->read_collection('respuestas', 500);;
        }

        // Build the HTTP response
        $response->getBody()->write((string)json_encode($resp));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
