<?php

use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;
use Repositories\UserRepository;

class PasswordAuthenticator implements Nette\Security\Authenticator
{
	private $passwords;
	private $userRepository;

	public function __construct(Passwords $passwords, UserRepository $userRepository)
	{
		$this->passwords = $passwords;
		$this->userRepository = $userRepository;
	}

	public function authenticate(string $username, string $password): IIdentity
	{
		$user = $this->userRepository->findByUsername($username);
		if (!$user) {
			throw new Nette\Security\AuthenticationException('User not found.');
		}
		if (!$this->passwords->verify($password, $user->getPassword())) {
			throw new Nette\Security\AuthenticationException('Invalid password.');
		}
		return $user;
	}
}
