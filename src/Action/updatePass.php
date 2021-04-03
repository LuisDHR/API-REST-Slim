<?php
namespace App\Action;

use App\Database\Firebase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class updatePass
{
    private $database;

    public function __construct()
    {
        $this->database = new Firebase();
    }

    public function __invoke(
        ServerRequestInterface $request, 
        ResponseInterface $response
    ): ResponseInterface {
        $requestData = $request->getParsedBody();
        $usuario = $requestData["user"];
        $pass = $requestData["pass"];
        $newPass = $requestData["newPass"];

        $resp = array(
            'code'    => 999,
            'message' => $this->database->read_collection('respuestas', 999),
            'data' => '',
            'status'  => 'error',
        );

        $res_usuario = $this->database->read_collection('usuarios', $usuario);
        if ( !is_null($res_usuario) ) {
            if ( $res_usuario === md5($pass) ) {
                // Validación de la nueva contraseña
                $tam = mb_strlen($newPass);
                if ( $tam >= 8 and preg_match("/[0-9]{1}/", $newPass) ) {
                    // Para la base de datos
                    $data = '"'.md5($newPass).'"';
                    $res = $this->database->update_collection('usuarios', $usuario, $data);

                    // Contraseña actualizada exitosamente
                    if( !is_null($res) ) {
                        $time = time();
                        $resp['code'] = 400;
                        $resp['message'] = $this->database->read_collection('respuestas', 400);
                        $resp['data'] = date("Y-m-d\TH:i:s", $time);
                        $resp['status'] = 'success';
                    }
                }
                else {
                    // Contraseña incorrecta: al menos 8 caracteres y al menos un número
                    $resp['code'] = 502;
                    $resp['message'] = $this->database->read_collection('respuestas', 502);
                }
            }
            else {
                // Password no reconcido
                $resp['code'] = 501;
                $resp['message'] = $this->database->read_collection('respuestas', 501);
            }
        }
        else {
            // Usuario no reconocido
            $resp['code'] = 500;
            $resp['message'] = $this->database->read_collection('respuestas', 500);
        }

        // Build the HTTP response
        $response->getBody()->write((string)json_encode($resp));

        return $response->withHeader('Content-Type', 'application/json');
    }
}