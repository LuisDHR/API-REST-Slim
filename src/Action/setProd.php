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

    public function login( $user, $pass ) {
        $resUser = $this->database->read_collection( 'usuarios', $user );
        $codeLogin = null;
        
        // Verify user
        if ( !is_null( $resUser ) ) {
            // Verify password
            $codeLogin = ( $resUser == md5( $pass ) ) ? null : 501;

            if ( is_null( $codeLogin ) )
            {
                $resUserDetails = json_encode( $this->database->read_collection( 'usuarios_info', $user ) );
                $objUserDetails = json_decode( $resUserDetails, true );
                
                // Check role
                $codeLogin = ( $objUserDetails[ 'rol' ] === 'ventas' ) ? null : 504;
            }
        } else {
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

    public function __invoke( ServerRequestInterface $request, ResponseInterface $response ): ResponseInterface
    {
        // Collect input from the HTTP request
        $data = $request->getParsedBody();
        $user = $data[ 'user' ];
        $pass = $data[ 'pass' ];
        $prodJSON = $data[ 'prodJSON' ];

        // Initial response data
        $code = 999;
        $data = '';
        $status = 'error';

        // Verify user, password and role
        $code = $this->login( $user, $pass, [ 'almacen', 'ventas' ] );

        if ( is_null( $code ) ) {
            // JSON syntax
            $json = json_decode( $prodJSON, true );
            $code = 305;

            if ( !is_null( $json ) ) {
                // JSON completeness
                $isbn = array_keys( $json )[ 0 ];
                $code = 304;

                if ( $this->validateJSON( $json[ $isbn ] ) ) {
                    // Non-existence of the product
                    $resExistencia = $this->database->read_collection( 'detalles', $isbn );
                    $code = 302;

                    if ( is_null( $resExistencia ) ) {
                        // Determine category
                        $categoria = '';
                        if ( strpos( $isbn, 'LBS' ) !== false ) {
                            $categoria = 'libros';
                        }
                        if ( strpos( $isbn, 'CMS' ) !== false ) {
                            $categoria = 'comics';
                        }
                        if ( strpos( $isbn, 'MGS' ) !== false ) {
                            $categoria = 'mangas';
                        }

                        // Create array prod
                        $prodArray = array();
                        $prodArray[ $isbn ] = $json[ $isbn ][ 'Nombre' ];

                        // Save to Database
                        $res1 = $this->database->create_document( 'detalles', $prodJSON );
                        $res2 = $this->database->create_collection( 'productos', $categoria, json_encode( $prodArray ) );

                        $code = 999;
                        if ( !is_null( $res1 ) && !is_null( $res2 ) ) {
                            $code = 202;
                            $data = date( 'Y-m-d\TH:i:s' );
                            $status = 'success';
                        }
                    }
                } 
            }
        }

        $resp = array(
            'code'    => $code,
            'message' => $this->database->read_collection( 'respuestas', $code ),
            'data'    => $data,
            'status'  => $status
        );

        // Build the HTTP response
        $response->getBody()->write( (string)json_encode( $resp ) );

        return $response->withHeader( 'Content-Type', 'application/json' );
    }
}
