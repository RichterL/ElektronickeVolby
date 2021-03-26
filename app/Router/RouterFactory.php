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
		$router->withModule('Frontend')
			->addRoute('//%domain%/prihlasit', 'Sign:in')
			->addRoute('//%domain%/odhlasit', 'Sign:out')
			->addRoute('//%domain%/<presenter>/<action>[/<id>]', 'Homepage:default')
			->end()
			->withModule('Backend')
			->addRoute('//admin.%domain%/<presenter>/<action>[/<id>]', 'Homepage:default');

		return $router;
	}
}
