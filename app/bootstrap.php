<?php

use Nette\Application\Routers\Route;

require __DIR__ . '/../libs/autoload.php';

$configurator = new Nette\Config\Configurator;

//$configurator->setDebugMode(TRUE);
$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../libs')
	->register();

$configurator->addConfig(__DIR__ . '/config.neon');
$container = $configurator->createContainer();

$container->router[] = new Route('<presenter>[/<action>][/<id>]', 'List:default');

return $container;
