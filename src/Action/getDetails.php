<?php 

namespace App\Action;

use App\Database\Firebase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class getDetails {
    private $database;

    public function __construct()
    {
        $this->database = new Firebase;
        
    }

    public function loggin($user, $pass, $roles)
    {
        $resUser = $this->database->read_collection('usuarios', $user);
        $codeLogin = null;

        if( !is_null($resUser) )
        {
            $codeLogin = ( $resUser == md5($pass) ) ? null : '501';

            if ( is_null($codeLogin) )
            {
                $resUserDetails = json_encode( $this->database->read_collection('usuarios_info', $user) );
                $objUserDetails = json_decode( $resUserDetails, true );  
                $codeLogin = in_array( $objUserDetails["rol"], $roles ) ? null : '504';
            } 
        }
        else {
            $codeLogin = '500';
        }

        return $codeLogin;
    }
    

    public function __invoke( ServerRequestInterface $request , ResponseInterface $response, $args ): ResponseInterface
    {
        $user = $args['user'];
        $pass = $args['pass'];
        $isbn = $args['isbn'];

        $code = 999; 
        $data = '';
        $status = 'error';

        $code= $this->loggin($user, $pass, ['almacen', 'ventas']);

        if ($code == null) {
            $resDetails = $this->database->read_collection('detalles', $isbn);
            $code = ( !is_null($resDetails) ) ? '201' : '301';

            if($code == '201') {
                $data = $resDetails;
                $status = 'seccess';
            }
        }
        
        $resp = array(
            'code'      => $code,
            'message'   => $this->database->read_collection('respuestas', $code),
            'data'      => $data,
            'status'    => $status,
            'oferta'    => ''
        );

        // built the HTTP response
        $response->getBody()->write((string)json_encode($resp));

        return $response->withAddedHeader('Content-Type', 'application/json');
    }
}


// $ejemplo = new getDetails();
// var_dump(['asdas', 'ventas']);
// echo $ejemplo->loggin('pruebas1', '12345678a', ['asdas', 'ventas']);

