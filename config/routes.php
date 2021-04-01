<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

return function (App $app) {
    $app->get('/', \App\Action\HomeAction::class);
    $app->get('/getProd/{user}/{pass}/{categoria}', \App\Action\getProd::class)->setName('getProd');
    $app->post('/setProd', \App\Action\setProd::class)->setName('setProd');
    $app->put('/updatePass', \App\Action\updatePass::class)->setName('updatePass');
};
