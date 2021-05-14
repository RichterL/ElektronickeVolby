<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\ElectionId;
use App\Models\Entities\Election\VoterFile;
use App\Models\Mappers\Exception\SavingErrorException;
use App\Models\Mappers\VoterMapper;
use App\Models\Entities\User;
use Ublaboo\DataGrid\DataSource\IDataSource;

class VoterRepository
{
	private VoterMapper $voterMapper;

	public function __construct(VoterMapper $voterMapper)
	{
		$this->voterMapper = $voterMapper;
	}

	public function importFromFile(Election $election, VoterFile $voterFile): bool
	{
		return $this->voterMapper->importFromFile($election, $voterFile);
	}

	public function getDataSource(array $filter = []): IDataSource
	{
		return $this->voterMapper->getDataSource($filter);
	}

	public function getCountTotal(Election $election): int
	{
		return $this->voterMapper->getCount($election);
	}

	public function getCountVoted(Election $election): int
	{
		return $this->voterMapper->getCount($election, true);
	}

	/**
	 * @throws SavingErrorException
	 */
	public function update(User $user, ElectionId $electionId): void
	{
		$this->voterMapper->update($user, $electionId);
	}

	public function hasVoted(User $user, Election $election): bool
	{
		return $this->voterMapper->hasVoted($user, $election);
	}
}
