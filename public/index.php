<?php

include '../lib/Autoloader.php';

$autoloader = App\Autoloader::create()
    ->loadClass('App\Util\Cli\Dump');

$app = new App\Application\Cli();
$app->setInjector(App\Container\Injector::create(array(

    array('autoloader', $autoloader,
        array('type' => App\Container\Injector::ENTITY_TYPE_OBJECT)),

    array('router', function(App\Config $config) {
        return new App\Router\Cli($config['router']['routes'],
            $config['router']['not_found']);
    }, array('type' => App\Container\Injector::ENTITY_TYPE_CALLBACK)),

    array('dispatcher', App\Dispatcher::class),
    array('config', App\Config::class, array(
        'params' => array('config' => '../config/config.php')
    )),

    array('database_driver',
        App\Database\Driver\PDOMySQL::class, array('shared' => false)),

    array('database', function(App\Database\Driver\PDOMySQL $driver,
        App\Config $config) {
        $dbConfig = $config['database'];
        $database = new App\Database($driver);
        $database->connect($dbConfig['host'], $dbConfig['user'], $dbConfig['pass'],
            $dbConfig['database'], $dbConfig['port']);
        foreach ($dbConfig['createQueries'] as $createQuery)
        {
            $database->query($createQuery);
        }
        return $database;
    }, array('type' => App\Container\Injector::ENTITY_TYPE_CALLBACK)),

    array('view', function(App\Config $config) {
        return new App\View($config['view']['dir']);
    }, array('type' => App\Container\Injector::ENTITY_TYPE_CALLBACK)),

    array('model_user', Model\User::class, array('shared' => false)),

)))->configure()->initialize()->dispatch();
