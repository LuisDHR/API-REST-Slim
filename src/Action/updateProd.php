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

    private function validateJson($json, $targets)
    {
        foreach ( $targets as &$value )
            if ( is_null($json[$value]) ||  strlen((string)$json[$value]) < 1 )
                return false;
        return true;
    }

    public function __invoke( ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $requestData = $request->getParsedBody();
        $user = $requestData['user'];
        $pass = $requestData['pass'];
        $json = $requestData['json'];

        $code = '999';
        $data = '';
        $status = 'error';

        $code = $this->loggin($user, $pass, ['almacen']);

        if ( $code == null )
        {
            // check json's sintax
            // $auxJson = json_decode(json_encode($json), true);
            $auxJson = $json;
            $code = $auxJson ? '203' : '305';

            if ( $code == '203') 
            {
                $isbn = array_keys($auxJson)[0]; 
                $resProduct = $this->database->read_document('detalles/'.$isbn);
                $code = is_null($resProduct) ? '303' : '203';
                
                if ( $code == '203') 
                {
                    $targets = array("isbn", "categoria", "autor", "nombre", "editorial", "año", "precio");
                    
                    $copyJson = array_change_key_case($auxJson[$isbn], CASE_LOWER);
                    $code = $this->validateJson($copyJson, $targets) ? '203' : '304';

                    // update product
                    if ( $code == '203' )
                    {
                        $dir = 'productos/'.$auxJson[$isbn]['Categoria'];
                        $arrayProduct = array($auxJson[$isbn]['ISBN'] => $auxJson[$isbn]['Nombre']);
                        $resUpdateProduct = $this->database->update_document($dir, json_encode($arrayProduct));
                        $code = is_null($resUpdateProduct) ? '999' : '203';
                    }

                    // update details
                    if ( $code == '203' )
                    {
                        $dir = 'detalles/'.$auxJson[$isbn]['Categoria'];
                        unset($auxJson[$isbn]['categoria']);
                        $resUpdateDetaail = $this->database->update_document($dir, json_encode($auxJson));
                        $code = is_null($resUpdateDetaail) ? '999' : '203';
                    }

                    if ( $code == '203')
                    {
                        $data = date('Y-m-s H:m:s');
                        $status = 'success';
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
