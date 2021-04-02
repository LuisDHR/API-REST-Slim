<?php
namespace App\Action;

use App\Database\Firebase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class setProd
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
        // Collect input from the HTTP request
        $data = $request->getParsedBody();
        $user = $data["user"];
        $pass = $data["pass"];
        $prodJSON = $data["prodJSON"];

        $keys = array('Autor', 'Editorial', 'ISBN', 'Nombre', 'Precio', 'Año');
        $isbn = '';

        $resp = array(
            'code'    => 999,
            'message' => $this->database->read_collection('respuestas', 999),
            'data'    => '',
            'status'  => 'error',
        );

        $res_usuario = $this->database->read_collection('usuarios', $user);
        if ( !is_null($res_usuario) ) {
            if ( $res_usuario === md5($pass) ) {
                // Sintaxis del JSON
                $json = json_decode($prodJSON, true);
                if ( !is_null($json) ) {
                    // Completitud del JSON
                    $esta_completo = true;
                    $prod_array = array();

                    foreach ($json as $key => $value) {
                        $array_keys = array_keys($value);
                        if ( empty(array_diff($keys, $array_keys)) ) {
                            $isbn = $key;
                            foreach ($value as $k => $v) {
                                if ( empty($v) ) {
                                    $esta_completo = false;
                                    break;
                                }
                                if ($k == 'Nombre') {
                                    $prod_array[$key] = $v;
                                }
                            }
                        } else {
                            $esta_completo = false;
                        }
                    }
                    
                    if ($esta_completo) {
                        // No existencia de producto
                        $res_existencia = $this->database->read_collection('detalles', $isbn);
                        if ( is_null($res_existencia) ) {
                            // Determinar si es libro, comic o manga
                            $categoria = '';
                            if (strpos($isbn, 'LBS') !== false) {
                                $categoria = 'libros';
                            }
                            if (strpos($isbn, 'CMS') !== false) {
                                $categoria = 'comics';
                            }
                            if (strpos($isbn, 'MGS') !== false) {
                                $categoria = 'mangas';
                            }

                            // Registrar en la base de datos
                            $res1 = $this->database->create_document('detalles', $prodJSON);
                            $res2 = $this->database->create_collection('productos', $categoria, json_encode($prod_array));

                            if( !is_null($res1) && !is_null($res2) ) {
                                $time = time();
                                // Producto registrado correctamente
                                $resp['code'] = 202;
                                $resp['message'] = $this->database->read_collection('respuestas', 202);
                                $resp['status'] = 'success';
                                $resp['data'] = date("Y-m-d\TH:i:s", $time);
                            }
                        } else {
                            // El ISBN ya existe
                            $resp['code'] = 302;
                            $resp['message'] = 'ERROR: El ISBN '.$isbn.' ya existe.';
                        }

                    } else {
                        // Faltan datos del producto en el JSON
                        $resp['code'] = 304;
                        $resp['message'] = $this->database->read_collection('respuestas', 304);
                    }
                }
                else {
                    // El JSON del producto está mal formado
                    $resp['code'] = 305;
                    $resp['message'] = $this->database->read_collection('respuestas', 305);
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

        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}