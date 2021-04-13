<?php
declare(strict_types=1);

namespace App\Models\Entities\Rule;

use App\Core\Utils\ValueObject\EnumTrait;
use App\Core\Utils\ValueObject\ValueObject;

class Type implements ValueObject
{
	public const ALLOW = 1;
	public const DENY = 2;

	private array $names = [
		self::ALLOW => 'allow',
		self::DENY => 'deny',
	];

	use EnumTrait;

	public function getName(): string
	{
		return $this->names[$this->getValue()];
	}
}
