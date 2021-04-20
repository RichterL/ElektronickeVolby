<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

class Results implements \JsonSerializable
{
	private ?array $data;

	public function __construct(?array $data)
	{
		$this->data = $data;
	}

	public function getData(): ?array
	{
		return $this->data;
	}

	public function jsonSerialize(): ?array
	{
		return $this->data ?? null;
	}
}
