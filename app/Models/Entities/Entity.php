<?php
declare(strict_types=1);

namespace Models\Entities;

use InvalidArgumentException;

abstract class Entity
{
	public function setValues(array $values): void
	{
		foreach ($values as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (method_exists($this, $method)) {
				$this->$method($value);
				continue;
			}
			if (property_exists($this, $key) && (empty($this->$key) || $this->$key !== $value)) {
				$this->$key = $value;
			}
		}
	}

	public function __get(string $key)
	{
		$method = 'get' . ucfirst($key);
		if (method_exists($this, $method)) {
			return $this->$method() ?? null;
		}
		if (property_exists($this, $key) && isset($this->$key)) {
			return $this->$key ?? null;
		}
	}

	public function __set(string $key, $value): void
	{
		$method = 'set' . ucfirst($key);
		if (method_exists($this, $method)) {
			$this->$method($value);
			return;
		}
		if (property_exists($this, $key)) {
			$this->$key = $value;
			return;
		}
		throw new InvalidArgumentException('Invalid property ' . get_called_class() . '::$' . $key);
	}

	public function __isset(string $key): bool
	{
		$ret = isset($this->$key);
		return $ret;
	}
}
