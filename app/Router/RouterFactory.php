<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		if ($_SERVER['SERVER_NAME'] == 'admin.volby.l') {
			$router->withModule('Backend')
				->addRoute('//admin.%domain%/prihlasit', 'Sign:in')
				->addRoute('//admin.%domain%/odhlasit', 'Sign:out')
				->addRoute('//admin.%domain%/<presenter>/<action>[/<id>]', 'Homepage:default');
		}
		if ($_SERVER['SERVER_NAME'] == 'admin.volby.lukasrichter.eu') {
			$router->withModule('Backend')
				->addRoute('//admin.volby.%domain%/prihlasit', 'Sign:in')
				->addRoute('//admin.volby.%domain%/odhlasit', 'Sign:out')
				->addRoute('//admin.volby.%domain%/<presenter>/<action>[/<id>]', 'Homepage:default');
		}

		$router->withModule('Frontend')
				->addRoute('/prihlasit', 'Sign:in')
				->addRoute('/odhlasit', 'Sign:out')
				->addRoute('/<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}
