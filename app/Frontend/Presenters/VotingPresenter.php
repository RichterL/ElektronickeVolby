<?php
declare(strict_types=1);

namespace App\Frontend\Presenters;

use App\Forms\Voting\VotingForm;
use App\Forms\Voting\VotingFormFactory;
use App\Frontend\Classes\ElectionsFacade;
use App\Models\Entities\Election\ElectionId;
use App\Models\Entities\Election\EncryptedBallot;
use App\Models\Entities\Election\Election;
use App\Models\Mappers\Exception\EntityNotFoundException;
use Nette\Forms\Form;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Hash;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey;
use phpseclib3\Crypt\RSA\PublicKey;
use phpseclib3\Math\BigInteger;

class VotingPresenter extends BasePresenter
{
	private ElectionsFacade $electionsFacade;
	private ?Election $election = null;

	public function __construct(ElectionsFacade $electionsFacade)
	{
		parent::__construct();
		$this->electionsFacade = $electionsFacade;
	}

	public function actionDefault(int $id): void
	{
		try {
			$this->election = $this->electionsFacade->getElectionById($id);
			if ($this->electionsFacade->hasVoted($this->getUserEntity(), $this->election)) {
				$this->flashMessage('You already voted in this election!', 'warning');
				$this->redirect('Homepage:');
			}
		} catch (EntityNotFoundException $e) {
			$this->error('Election not found');
		}
	}

	public function renderDefault(): void
	{
		$this->template->election = $this->election;
	}

	/** @var VotingFormFactory @inject */
	public VotingFormFactory $votingFormFactory;

	public function createComponentVotingForm(): VotingForm
	{
		$form = $this->votingFormFactory->create();
		$form->setElection($this->election);
		$form->onError = function (Form $form) {
			$this->flashMessage('error');
		};
		$form->onSubmit = function () {
			$this->flashMessage('Form submitted directly without encrypting the vote, aborting.', 'error');
		};
//		$form->onSuccess[] = [$this, 'votingFormSuccess'];

		return $form;
	}

	public function votingFormSuccess($form, $values): void
	{
		$form->addError('This form should be sent through AJAX.');
	}

	public function handleGetPublicSigningKey(): void
	{
		$key = $this->election->getPublicSigningKey();
		$parts = $key->toString('raw');

		$this->sendJson([
			'n' => bin2hex($parts['n']->toBytes()),
			'e' => bin2hex($parts['e']->toBytes()),
			'key' => $key->__toString(),
		]);
	}

	public function handleGetPublicEncryptionKey(): void
	{
		$this->sendJson((string) $this->election->getPublicEncryptionKey());
	}

	/**
	 * padded_message = pad(message)
	 * blinded_message = padded_message⋅r^e (modN)
	 * blinded_signature = blinded_message^d = padded_message^d⋅r (modN)
	 * signature = blinded_signature⋅r^−1 = padded_message^d (modN)
	 */
	public function handleBlindSign(): void
	{
		/** @var PrivateKey $privateKey */
		$privateKey = $this->election->getPrivateSigningKey()->withPadding(RSA::ENCRYPTION_NONE);
		$privateKey::disableBlinding();
		$request = $this->getHttpRequest();
		$raw = $request->getRawBody();
		try {
			$parsed = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
			$int = new BigInteger($parsed['message'], 16);
			$binary = $int->toBytes();
			$signed = $privateKey->decrypt($binary);
			$hexSigned = bin2hex($signed);
			$this->sendJson(['message' => $hexSigned]);
		} catch (\JsonException $e) {
			$this->sendJson(['error' => $e->getMessage()]);
		}
	}

	public function handleSave(): void
	{
		$request = $this->getHttpRequest();
		$raw = $request->getRawBody();
		try {
			[
				'ballot' => $ballotData,
				'key' => $encryptedKey,
				'signature' => $signature
			] = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
		} catch (\JsonException $e) {
			$this->sendJson(['error' => $e->getMessage()]);
		}

		/** @var PrivateKey $privateKey */
		$privateKey = $this->election->getPrivateSigningKey()->withPadding(RSA::ENCRYPTION_NONE);
		$privateKey::disableBlinding();
		$hash = new Hash('sha256');
		$messageHash = $hash->hash($encryptedKey);
		$signed = $privateKey->decrypt($messageHash);
		$verified = $signature === bin2hex($signed);

		$ballot = new EncryptedBallot();
		$ballot->setValues([
			'election' => ElectionId::fromValue($this->election->getId()),
			'encryptedData' => $ballotData,
			'encryptedKey' => $encryptedKey,
			'hash' => bin2hex($messageHash),
			'signature' => $signature,
		]);

		try {
			if ($this->electionsFacade->hasVoted($this->getUserEntity(), $this->election)) {
				$this->sendJson([
					'error' => 'You already voted in this election.',
				]);
			}
			$this->electionsFacade->saveBallot($this->getUserEntity(), $ballot);
		} catch (\Exception $e) {
			$this->sendJson([
				'error' => $e->getMessage(),
			]);
		}

		$this->sendJson([
			'status' => 'ok',
		]);
	}
}
