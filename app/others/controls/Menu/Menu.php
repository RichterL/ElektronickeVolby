<?php

namespace App\Controls;

use Nette\Application\UI\Control;

class Menu extends Control
{
	private $user;

	private const MENU_ITEMS = [
		[
			'name' => 'Homepage',
			'link' => 'Homepage:',
			'icon' => 'cil-speedometer',
		],
		[
			'name' => 'ACL',
			'icon' => 'cil-people',
			'childs' => [
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
			],
		],
	];

	public function render()
	{
		$this->user = $this->presenter->getUser();
		$this->checkPermissions();
		$this->template->render(__DIR__ . '/menu.latte');
	}

	private function checkPermissions()
	{
		$tmp = [];
		foreach (self::MENU_ITEMS as $item) {
			if ($this->checkItem($item)) {
				$tmp[] = $item;
			}
		}

		$this->template->items = $tmp;
	}

	private function checkItem(array $item)
	{
		$tmp = $item;
		unset($tmp['childs']);
		if (array_key_exists('resource', $item)) {
			if (!$this->user->isAllowed($item['resource'], $item['privilege'])) {
				return $tmp;
			}
		}
		if (!empty($item['childs'])) {
			foreach ($item['childs'] as $child) {
				if ($this->checkItem($child)) {
					$tmp['childs'] = $child;
				}
			}
		}
		return $tmp;
	}
}
