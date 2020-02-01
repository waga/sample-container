<?php

return array(
    'database' => array(
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'database' => 'db_name',
        'port' => 3306,
        'createQueries' => array(
            'user' => '-- CREATE TABLE IF NOT EXISTS `user` ...'
        )
    ),
    'router' => array(
        'not_found' => array('dispatcher' => array('Error', 'notFound')),
        'routes' => array(
            array('url' => '/', 'dispatcher' => array('Home', 'index'), 'method' => 'GET'),
            array('url' => '/request', 'dispatcher' => array('Request', 'index'), 'method' => 'GET'),
        )
    ),
    'controller' => array(
        'namespace' => 'Controller',
        'dir' => dirname(__FILE__) .'/../Controller'
    ),
    'model' => array(
        'namespace' => 'Model',
        'dir' => dirname(__FILE__) .'/../Model'
    ),
    'view' => array(
        'dir' => '../view/'
    ),
    'temp' => array(
        'dir' => dirname(__FILE__) .'/../temp/'
    )
);
