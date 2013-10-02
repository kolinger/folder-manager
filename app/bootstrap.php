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

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon', $configurator::NONE);
$container = $configurator->createContainer();

$defaults = $container->parameters['defaults'];
$metadata = array(
	'presenter' => 'List',
	'action' => 'default',
	'sort' => $defaults['sort'],
	'sortType' => $defaults['sortType'],
	'path' => $defaults['path'],
);

$flags = $container->parameters['useHttps'] ? Route::SECURED : 0;
$container->router[] = new Route('<presenter>[/<action>][/<id>]', 'List:default', $flags);

return $container;
