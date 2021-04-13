<?php

declare(strict_types=1);

/**
 * Inspired by https://github.com/funeralzone/valueobjects
 */

namespace App\Core\Utils\ValueObject;

interface ValueObject
{
	public function isNull(): bool;

	public function equals(ValueObject $object): bool;

	public static function fromValue($value);

	public function getValue();

	public function __toString();
}
