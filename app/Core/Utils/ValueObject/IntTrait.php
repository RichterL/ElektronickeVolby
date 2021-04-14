<?php

declare(strict_types=1);

namespace App\Core\Utils\ValueObject;

trait IntTrait
{
	protected int $value;

	public function __construct(int $value)
	{
		$this->value = $value;
	}

	public function equals(ValueObject $other): bool
	{
		return $this->getValue() === $other->getValue();
	}

	public function isNull(): bool
	{
		return false;
	}

	public static function fromValue($value)
	{
		if (!is_int($value)) {
			throw new \InvalidArgumentException('Only integers are allowed, got ' . gettype($value));
		}
		return new static($value);
	}

	public function getValue()
	{
		return $this->value;
	}
}
