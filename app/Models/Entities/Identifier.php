<?php

namespace App\Models\Entities;

use App\Core\Utils\ValueObject\IntTrait;
use App\Core\Utils\ValueObject\ValueObject;

class Identifier implements ValueObject
{
	use IntTrait;

	public function __toString(): string
	{
		return (string) $this->value;
	}
}
