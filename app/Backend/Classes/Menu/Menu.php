<?php
declare(strict_types=1);

namespace App\Backend\Classes\Menu;

use Nette\Application\UI\Control;
use Nette\Security\User;

class Menu extends Control
{
	private User $user;

	private const MENU_ITEMS = [
		[
			'name' => 'Homepage',
			'link' => 'Homepage:',
			'icon' => 'home',
		],
		[
			'name' => 'ACL',
			'icon' => 'user-shield',
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
		[
			'name' => 'Elections',
			'link' => 'Elections:',
			'icon' => 'clipboard',
		],
	];

	public function render(): void
	{
		$this->user = $this->presenter->getUser();
		$this->checkPermissions();
		$this->template->render(__DIR__ . '/menu.latte');
	}

	private function checkPermissions(): void
	{
		$tmp = [];
		foreach (self::MENU_ITEMS as $item) {
			if ($this->checkItem($item)) {
				$tmp[] = $item;
			}
		}

		$this->template->items = $tmp;
	}

	private function checkItem(array $item): array
	{
		$tmp = $item;
		unset($tmp['childs']);
		if (array_key_exists('resource', $item)
			&& !$this->user->isAllowed($item['resource'], $item['privilege'])
		) {
			return $tmp;
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
