<?php

declare(strict_types=1);

/**
 * Inspired by https://github.com/funeralzone/valueobjects
 * and an answer at SO: https://stackoverflow.com/a/254543
 *
 */

namespace App\Core\Utils\ValueObject;

trait EnumTrait
{
	private static $constantsCache = null;

	public function __construct($value)
	{
		if (!static::isValidValue($value)) {
			throw new \InvalidArgumentException($value . ' is not one of allowed values for ' . get_called_class() . ' value object.');
		}
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

	public function getValue()
	{
		return $this->value;
	}

	public function __toString()
	{
		return (string) $this->getValue();
	}

	public static function __callStatic($name, $args)
	{
		return static::fromKey($name);
	}

	public static function fromValue($value)
	{
		if (!static::isValidValue($value)) {
			throw new \InvalidArgumentException($value . ' is not one of the allowed values for ' . get_called_class() . ' object');
		}
		return new static($value);
	}

	public static function fromKey(string $key)
	{
		if (!static::isValidKey($key)) {
			throw new \InvalidArgumentException($key . ' is not one of the allowed keys for ' . get_called_class() . ' object');
		}

		return new static(static::$constantsCache[strtoupper($key)]);
	}

	public static function isValidValue($value, $strict = true): bool
	{
		return in_array($value, array_values(static::getConstants()), $strict);
	}

	public static function isValidKey($key, $strict = false)
	{
		$constants = self::getConstants();

		if ($strict) {
			return array_key_exists($key, $constants);
		}

		$keys = array_map('strtolower', array_keys($constants));
		return in_array(strtolower($key), $keys);
	}

	private static function getConstants()
	{
		if (static::$constantsCache === null) {
			$rc = new \ReflectionClass(get_called_class());
			static::$constantsCache = $rc->getConstants();
		}
		return static::$constantsCache;
	}
}
