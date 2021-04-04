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

    public function __invoke( ServerRequestInterface $request, ResponseInterface $response ): ResponseInterface
    {
        // Collect input from the HTTP request
        $requestData = $request->getParsedBody();
        $user = $requestData[ 'user' ];
        $pass = $requestData[ 'pass' ];
        $newPass = $requestData[ 'newPass' ];

        // Initial response data
        $code = 999;
        $data = '';
        $status = 'error';

        // Verify user, password and role
        $code = $this->login( $user, $pass, [ 'almacen', 'ventas' ] );

        if ( is_null( $code ) ) {
            // New password validation
            $tam = mb_strlen( $newPass );
            $code = 502;

            if ( $tam >= 8 and preg_match( "/[0-9]{1}/", $newPass ) ) {
                // Save to database
                $newData = '"'.md5( $newPass ).'"';
                $res = $this->database->update_collection( 'usuarios', $user, $newData );
                $code = 999;

                // Password updated successfully
                if( !is_null( $res ) ) {
                    $code = 400;
                    $data = date( 'Y-m-d\TH:i:s' );
                    $status = 'success';
                }
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