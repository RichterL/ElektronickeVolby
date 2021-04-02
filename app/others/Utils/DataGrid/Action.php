<?php
declare(strict_types=1);

namespace Utils\DataGrid;

class Action
{
	const VIEW = 'view';
	const EDIT = 'edit';
	const DELETE = 'delete';
	const DOWNLOAD = 'download';

	const ALL = [
		self::VIEW,
		self::EDIT,
		self::DELETE,
		self::DOWNLOAD,
	];

	public static function isValid(string $type)
	{
		return in_array($type, self::ALL);
	}
}
