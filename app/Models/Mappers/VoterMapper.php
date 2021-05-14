<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\ElectionId;
use App\Models\Entities\Election\VoterFile;
use App\Models\Entities\User;
use App\Models\Mappers\Exception\SavingErrorException;
use Ublaboo\DataGrid\DataSource\IDataSource;

interface VoterMapper
{
	public function importFromFile(Election $election, VoterFile $voterFile): bool;

	public function deleteRelated(Election $election): bool;

	public function getDataSource(array $filter = []): IDataSource;

	public function getCount(Election $election, bool $voted = false): int;

	/**
	 * @throws SavingErrorException
	 */
	public function update(User $user, ElectionId $electionId): void;

	public function hasVoted(User $user, Election $election): bool;
}
