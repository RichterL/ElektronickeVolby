<?php

namespace App\Controls;

use Nette\Application\UI\Control;

class Menu extends Control
{
	private const MENU_ITEMS = [
		[
			'name' => 'Homepage',
			'link' => 'Homepage:',
			'icon' => null,
		],
		[
			'name' => 'Users',
			'link' => 'Users:',
			'icon' => null,
			'resource' => 'users',
			'privilege' => 'view',
		],
		[
			'name' => 'Roles',
			'link' => 'Roles:',
			'icon' => null,
			'resource' => 'roles',
			'privilege' => 'view',
		],
		[
			'name' => 'Resources',
			'link' => 'Resources:',
			'icon' => null,
			'resource' => 'resources',
			'privilege' => 'view',
		],
		[
			'name' => 'Sign out',
			'link' => 'Sign:out',
			'icon' => null,
		],
	];

	public function render()
	{
		$this->checkPermissions();
		$this->template->render(__DIR__ . '/menu.latte');
	}

	public function checkPermissions()
	{
		$tmp = [];
		$user = $this->presenter->getUser();
		foreach (self::MENU_ITEMS as $item) {
			if (array_key_exists('resource', $item)) {
				if (!$user->isAllowed($item['resource'], $item['privilege'])) {
					continue;
				}
			}
			$tmp[] = $item;
		}

		$this->template->items = $tmp;
	}
}
