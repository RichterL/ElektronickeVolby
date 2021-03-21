<?php

namespace Models\Entities\Rule;

use Utils\ValueObject\EnumTrait;
use Utils\ValueObject\ValueObject;

class Type implements ValueObject
{
	const ALLOW = 1;
	const DENY = 2;

	private $names = [
		self::ALLOW => 'allow',
		self::DENY => 'deny',
	];

	use EnumTrait;

	public function getName()
	{
		return $this->names[$this->getValue()];
	}
}
