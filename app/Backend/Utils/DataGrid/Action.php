<?php
declare(strict_types=1);

namespace App\Backend\Utils\DataGrid;

class Action
{
	public const VIEW = 'view';
	public const EDIT = 'edit';
	public const DELETE = 'delete';
	public const DOWNLOAD = 'download';
	public const APPLY = 'apply';

	public const ALL = [
		self::VIEW,
		self::EDIT,
		self::DELETE,
		self::DOWNLOAD,
		self::APPLY,
	];

	public static function isValid(string $type): bool
	{
		return in_array($type, self::ALL);
	}
}
