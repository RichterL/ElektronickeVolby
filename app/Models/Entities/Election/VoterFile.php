<?php
declare(strict_types=1);

namespace App\Models\Entities\Election;

use DateTimeInterface;
use App\Models\Entities\Entity;
use App\Models\Entities\IdentifiedById;
use App\Models\Entities\User;
use App\Models\Traits\Entity\HasId;

/**
 * @property int|null $id
 * @property Election $election
 * @property string $filename
 * @property string|null $content
 * @property DateTimeInterface $createdAt
 * @property User $createdBy
 */

class VoterFile extends Entity implements IdentifiedById
{
	protected ?int $id = null;
	protected Election $election;
	protected string $filename;
	protected string $content;
	protected DateTimeInterface $createdAt;
	protected User $createdBy;

	private bool $contentCompressed;

	use HasId;

	public function setContent(string $content, bool $compressed = false)
	{
		if (!$compressed) {
			$content = gzencode($content);
		}
		$this->content = $content;
	}

	public function getContent(bool $compressed = false): string
	{
		return $compressed ? $this->content : gzdecode($this->content);
	}

	public function setElection(Election $election): self
	{
		$this->election = $election;
		return $this;
	}
}
