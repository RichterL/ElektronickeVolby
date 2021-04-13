<?php
declare(strict_types=1);

namespace App\Frontend\Presenters;

use App\Forms\Voting\VotingForm;
use App\Forms\Voting\VotingFormFactory;
use App\Frontend\Classes\ElectionsFacade;
use App\Models\Entities\Election\EncryptedBallot;
use App\Models\Entities\Election\Election;
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
		$this->election = $this->electionsFacade->getElectionById($id);
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

		return $form;
	}

	public function getPrivateSigningKey(): PrivateKey
	{
		$privatefile = file_get_contents('../keys/key.pem');
		return PublicKeyLoader::load($privatefile);
	}

	public function getPublicKey(): PublicKey
	{
		$publicFile = file_get_contents('../keys/public.pem');
		return PublicKeyLoader::load($publicFile);
	}

	public function handleGetPublicSigningKey(): void
	{
		$publicFile = file_get_contents('../keys/public.pem');
		$key = PublicKeyLoader::load($publicFile);
		$parts = $key->toString('raw');

		$this->sendJson([
			'n' => bin2hex($parts['n']->toBytes()),
			'e' => bin2hex($parts['e']->toBytes()),
			'key' => $key->__toString(),
		]);
	}

	public function handleGetPublicEncryptionKey(): void
	{
		$publicfile = file_get_contents('../keys/public.pem');
		$public = PublicKeyLoader::load($publicfile);
		$key = $public->__toString();
		$this->sendJson($key);
	}

	/**
	 * padded_message = pad(message)
	 * blinded_message = padded_message⋅r^e (modN)
	 * blinded_signature = blinded_message^d = padded_message^d⋅r (modN)
	 * signature = blinded_signature⋅r^−1 = padded_message^d (modN)
	 */
	public function handleBlindSign()
	{
		/** @var PrivateKey $privateKey */
		$privateKey = PublicKeyLoader::load(file_get_contents('../keys/key.pem'));
		/** @var PrivateKey $privateKey */
		$privateKey = $privateKey->withPadding(RSA::ENCRYPTION_NONE);
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
		$privateKey = $this->getPrivateSigningKey()
			->withPadding(RSA::ENCRYPTION_NONE);
		$privateKey::disableBlinding();
		$hash = new Hash('sha256');
		$messageHash = $hash->hash($encryptedKey);
		$signed = $privateKey->decrypt($messageHash);
		$verified = $signature === bin2hex($signed);

		$ballot = new EncryptedBallot();
		$ballot->setValues([
			'election' => $this->election,
			'encryptedData' => $ballotData,
			'encryptedKey' => $encryptedKey,
			'hash' => bin2hex($messageHash),
			'signature' => $signature,
		]);

		try {
			$this->electionsFacade->saveBallot($ballot);
		} catch (\Exception $e) {
			$this->sendJson([
				'error' => $e->getMessage(),
			]);
		}

		$this->sendJson([
			'status' => 'ok',
		]);
	}

	/** decrypting ballot received by user directly (temporary for testing) */
	public function decrypt(string $key, string $ballot): void
	{
		$privateKey = $this->getPrivateSigningKey();
		$decrypted = $privateKey->decrypt(base64_decode($key));
		['key' => $decryptingKey, 'iv' => $iv] = json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);

		$aes = new AES('gcm');
		$ballotBin = base64_decode($ballot);
		$aes->setKey(base64_decode($decryptingKey));
		$aes->setNonce(base64_decode($iv));
		$aes->setTag(substr($ballotBin, -16));
		$decrypted = $aes->decrypt(substr($ballotBin, 0, -16));
		$decrypted = json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);

		$this->sendJson([
			'status' => 'ok',
			'decryptedKey' => $decryptingKey,
			'iv' => $iv,
			'decryptedMessage' => $decrypted,
		]);
	}

	public function base64_encode_url($string)
	{
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
	}

	public function base64_decode_url($string)
	{
		return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
	}
}
