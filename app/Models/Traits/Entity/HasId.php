<?php
declare(strict_types=1);

namespace Models\Traits\Entity;

trait HasId
{
	protected ?int $id = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): self
	{
		$this->id = $id;
		return $this;
	}
}