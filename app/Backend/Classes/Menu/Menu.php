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
			'resource' => 'elections',
			'privilege' => 'view',
		],
	];

	public function render(): void
	{
		$this->user = $this->presenter->getUser();
		$this->template->items = $this->load();
		$this->template->render(__DIR__ . '/menu.latte');
	}

	private function load(): array
	{
		$menu = [];
		foreach (self::MENU_ITEMS as $item) {
			if (array_key_exists('resource', $item)
				&& !$this->user->isAllowed($item['resource'], $item['privilege'])
			) {
				continue;
			}
			if (!empty($item['childs'])) {
				$tmp = [];
				foreach ($item['childs'] as $child) {
					if (array_key_exists('resource', $child)
						&& $this->user->isAllowed($child['resource'], $child['privilege'])
					) {
						$tmp[] = $child;
					}
				}
				$item['childs'] = $tmp;
			}
			$menu[] = $item;
		}
		return $menu;
	}
}
