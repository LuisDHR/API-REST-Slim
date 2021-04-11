<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

return function (App $app) {
    // getProd
    $app->get( '/products/{user}/{pass}/{categoria}', \App\Action\getProd::class);
    // getDetails
    $app->get( '/details/{user}/{pass}/{isbn}', \App\Action\getDetails::class);
    // deleteProd
    $app->delete( '/products/{user}/{pass}/{isbn}', \App\Action\deleteProd::class );
    // setProd
    $app->post( '/products', \App\Action\setProd::class )->setName( 'setProd' );
    // updateProd
    $app->put( '/products/updateProd', \App\Action\updateProd::class )->setName( 'updateProd' );
    // updatePass
    $app->put( '/users/updatePass', \App\Action\updatePass::class )->setName( 'updatePass' );
};
