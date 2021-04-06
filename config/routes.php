<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

return function (App $app) {
    $app->get( '/', \App\Action\HomeAction::class );
    $app->get( '/products/{user}/{pass}/{categoria}', \App\Action\getProd::class);
    $app->get( '/details/{user}/{pass}/{isbn}', \App\Action\getDetails::class);
    $app->delete( '/products/{user}/{pass}/{isbn}', \App\Action\deleteProd::class );
    $app->post( '/products', \App\Action\setProd::class )->setName( 'setProd' );
    $app->put( '/products/updateProd', \App\Action\updateProd::class )->setName( 'updateProd' );
    $app->put( '/users/updatePass', \App\Action\updatePass::class )->setName( 'updatePass' );
};
