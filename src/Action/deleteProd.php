<?php

namespace App\Action;       

use App\Database\Firebase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class deleteProd 
{
    private $database;

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
            $codeLogin = ( $resUser == md5($pass) ) ? null : '501';

            if ( is_null($codeLogin) )
            {
                $resUserDetails = json_encode( $this->database->read_collection('usuarios_info', $user) );
                $objUserDetails = json_decode( $resUserDetails, true );  
                $codeLogin = in_array( $objUserDetails["rol"], $roles ) ? null : '504';
            } 
        }
        else 
        {
            $codeLogin = '500';
        }

        return $codeLogin;
    }
 

    public function __invoke( ServerRequestInterface $request, ResponseInterface $reponse ): ResponseInterface
    {
        $requesData = $request->getParsedBody();
        $user = $requesData['user'];
        $pass = $requesData['pass'];
        $isbn = $requesData['isbn'];

        $code = '999';
        $data = '';
        $status = 'error';

        $code = $this->loggin($user, $pass, ['almacen']);

        if ( $code == null )
        {
            $resDetails = $this->database->read_document('detalles/'.$isbn);
            $code = is_null($resDetails) ? '303' : '204';

            if ($code == '204')
            {
                $categoria = strtolower($isbn);
                $dir = '';
                switch(str_split($categoria)[0])
                {
                    case 'c': $dir = 'productos/comics/'; break;
                    case 'l': $dir = 'productos/libros/'; break;
                    case 'm': $dir = 'productos/mangas/'; break;
                }

                $resDeleteDetails = $this->database->delete_document($dir.$isbn);
                $code = $resDeleteDetails ? '204' : '999';
            }
            
            if ( $code == '204' )
            {
                $resDeleteProducto = $this->database->delete_document('detalles/'.$isbn);
                $code = $resDeleteProducto ? '204' : '999';
            }

            if ($code == '204') {
                $data = date('Y-m-d\TH:i:s');
                $status = 'success';
            }
        }

        $res = array(
            "code"  => $code,
            "message" => $this->database->read_collection('respuestas', $code),
            "data" => $data,
            "status" => $status
        );

        $reponse->getBody()->write((string)json_encode($res));
        return $reponse->withAddedHeader('Content-Type', 'application/json');        
    }
}

?>
