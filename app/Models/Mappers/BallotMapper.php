<?php
declare(strict_types=1);

namespace App\Models\Mappers;

use App\Models\Entities\Election\Ballot;
use App\Models\Entities\Election\DecryptedBallot;
use App\Models\Entities\Election\Election;
use App\Models\Entities\Election\EncryptedBallot;
use App\Models\Mappers\Exception\EntityNotFoundException;
use App\Models\Mappers\Exception\SavingErrorException;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;

interface BallotMapper extends TransactionableMapper
{
	/**
	 * @throws EntityNotFoundException
	 */
	public function findOne(array $filter = []): Ballot;

	/**
	 * @return Ballot[]
	 */
	public function find(array $filter = []): iterable;

	/**
	 * @return EncryptedBallot[]
	 */
	public function findEncrypted(Election $election): iterable;

	/**
	 * @return DecryptedBallot[]
	 */
	public function findDecrypted(Election $election): iterable;

	/**
	 * @throws SavingErrorException
	 */
	public function save(Ballot $ballot): bool;

	public function delete(Ballot $ballot): bool;

	public function getDataSource(array $filter = []): DibiFluentDataSource;
}
