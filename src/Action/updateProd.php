<?php

namespace App\Action;

use App\Database\Firebase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class updateProd
{
    private $datebase;

    public function __construct()
    {
        $this->database = new Firebase();
    }

    public function loggin($user, $pass, $roles)
    {
        $resUser = $this->database->read_collection('usuarios', $user);
        $codeLogin = null;

        if( !is_null($resUser) )
        {
            $codeLogin = ( $resUser == md5($pass) ) ? null : 501;

            if ( is_null($codeLogin) )
            {
                $resUserDetails = json_encode( $this->database->read_collection('usuarios_info', $user) );
                $objUserDetails = json_decode( $resUserDetails, true );  
                $codeLogin = in_array( $objUserDetails["rol"], $roles ) ? null : 504;
            } 
        }
        else {
            $codeLogin = 500;
        }

        return $codeLogin;
    }

    public function validateJSON( $json ) {
        $keys = array( 'Autor', 'Editorial', 'ISBN', 'Nombre', 'Precio', 'Year' );
        $jsonKeys = array_keys( $json );

        if ( !empty( array_diff( $keys, $jsonKeys ) ) ) {
            return false;
        }

        foreach ( $keys as $key ) {
            if ( is_null( $json[ $key ] ) || empty( $json[ $key ] ) ) {
                return false;
            }
        }

        return true;
    }

    public function __invoke( ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $requestData = $request->getParsedBody();
        $user = $requestData['user'];
        $pass = $requestData['pass'];
        $json = $requestData['prodJSON'];

        $code = 999;
        $data = '';
        $status = 'error';

        $code = $this->loggin($user, $pass, ['almacen']);

        if ( is_null( $code ) )
        {
            // check json's sintax
            $auxJson = json_decode($json, true);
            $code = 305;

            if ( !is_null( $auxJson ) )
            {
                $isbn = array_keys($auxJson)[0];
                $code = 304;

                if ( $this->validateJSON( $auxJson[ $isbn ] ) ) {
                    // Existence of the product
                    $resExistencia = $this->database->read_collection( 'detalles', $isbn );
                    $code = 303;

                    if ( !is_null( $resExistencia ) ) {
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

                        // Name
                        $name = '"'.$auxJson[ $isbn ][ 'Nombre' ].'"';

                        // Registrar en la base de datos
                        $res1 = $this->database->update_collection( 'detalles', $isbn, json_encode( $auxJson[ $isbn ] ) );
                        $res2 = $this->database->update_collection( 'productos', $categoria.'/'.$isbn, $name );

                        $code = 999;
                        if ( !is_null( $res1 ) && !is_null( $res2 ) ) {
                            $code = 203;
                            $data = date( 'Y-m-d\TH:i:s' );
                            $status = 'success';
                        }
                    }
                }
            } 
        }
        
        $res = array(
            'code'      => $code,
            'message'   => $this->database->read_document('respuestas/'.$code),
            'data'      => $data,
            'status'    => $status
        );

        // built the http response
        $response->getBody()->write((string)json_encode($res));

        return $response->withAddedHeader('Content-Type', 'application/json');
    }
}
?>
