<?php
declare(strict_types=1);

namespace Models\Entities\Election;

use Constants;
use InvalidArgumentException;
use Models\Entities\Entity;
use Models\Entities\IdentifiedById;
use Models\Entities\User;

/**
 * @property int|null $id
 * @property string $title
 * @property string $description
 * @property bool $active
 * @property bool $secret
 * @property \DateTimeInterface $start
 * @property \DateTimeInterface $end
 * @property \DateTimeInterface $createdAt
 * @property User $createdBy
 */
class Election extends Entity implements IdentifiedById
{
	protected string $title;
	protected string $description;
	protected bool $active;
	protected bool $secret;
	protected \DateTimeInterface $start;
	protected \DateTimeInterface $end;
	protected \DateTimeInterface $createdAt;
	protected User $createdBy;

	use \Models\Traits\Entity\HasId;

	public function setStart($from): self
	{
		$this->start = $this->getDateTime($from);
		return $this;
	}

	public function setEnd($to): self
	{
		$this->end = $this->getDateTime($to);
		return $this;
	}

	public function setCreatedAt($datetime): self
	{
		$this->createdAt = $this->getDateTime($datetime);
		return $this;
	}

	private function getDateTime($value): \DateTimeInterface
	{
		if (is_string($value)) {
			$value = \DateTime::createFromFormat(Constants::DATETIME_FORMAT, $value);
		}
		if (!$value instanceof \DateTimeInterface) {
			throw new InvalidArgumentException('Datetime format not supported, use DateTime or string format ' . Constants::DATETIME_FORMAT);
		}
		return $value;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'title' => $this->title,
			'description' => $this->description,
			'active' => $this->active,
			'secret' => $this->secret,
			'start' => $this->start->format(Constants::DATETIME_FORMAT),
			'end' => $this->end->format(Constants::DATETIME_FORMAT),
			'createdAt' => $this->createdAt->format(Constants::DATETIME_FORMAT),
			'createdBy' => $this->createdBy->getId(),
		];
	}
}
