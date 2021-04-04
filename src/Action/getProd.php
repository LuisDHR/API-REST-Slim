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

    public function login( $user, $pass, $roles ) {
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
                $codeLogin = in_array( $objUserDetails[ 'rol' ], $roles ) ? null : 504;
            }
        } else {
            $codeLogin = 500;
        }

        return $codeLogin;
    }

    public function __invoke( ServerRequestInterface $request, ResponseInterface $response, $args ): ResponseInterface 
    {
        // Data received
        $user = $args[ 'user' ];
        $pass = $args[ 'pass' ];
        $category = $args[ 'categoria' ];

        // Category to lowercase
        $category = strtolower( $category );

        // Initial response data
        $code = 999;
        $data = '';
        $status = 'error';

        // Verify user, password and role
        $code = $this->login( $user, $pass, [ 'almacen', 'ventas' ] );

        if ( is_null( $code ) ) {
            // Check category
            $resProducts = $this->database->read_collection( 'productos', $category );
            $code =  300;

            if ( !is_null( $resProducts ) ) {
                $code = 200;
                $data = $resProducts;
                $status = 'success';
            }
        }

        $resp = array(
            'code' => $code,
            'message' => $this->database->read_collection( 'respuestas', $code ),
            'data' => $data,
            'status' => $status
        );

        // Build the HTTP response
        $response->getBody()->write( (string)json_encode( $resp ) );

        return $response->withHeader( 'Content-Type', 'application/json' );
    }
}
