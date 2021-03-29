<?php
declare(strict_types=1);

namespace Utils\DataGrid;

class Action
{
	const VIEW = 'view';
	const EDIT = 'edit';
	const DELETE = 'delete';

	const ALL = [
		self::VIEW,
		self::EDIT,
		self::DELETE,
	];

	public static function isValid(string $type)
	{
		return in_array($type, self::ALL);
	}
}
