<?php

use LDAP\LdapException;
use LDAP\Service;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;
use Repositories\UserRepository;

class LdapAuthenticator implements Nette\Security\Authenticator
{
	private $passwords;
	private $userRepository;
	private $ldapService;

	public function __construct(Passwords $passwords, UserRepository $userRepository, Service $service)
	{
		$this->passwords = $passwords;
		$this->userRepository = $userRepository;
		$this->ldapService = $service;
	}

	public function authenticate(string $username, string $password): SimpleIdentity
	{
		try {
			$remoteUser = $this->ldapService->getRemoteUser($username, $password);
		} catch (LdapException $ex) {
			throw new Nette\Security\AuthenticationException('Authentication failed.');
		}

		$user = $this->userRepository->findByUsername($remoteUser['email']);
		if ($user) {
			return new SimpleIdentity(
				$user->getId(),
				$user->getRolesNames(),
				['name' => $user->getUsername()]
			);
		}

		return new SimpleIdentity(
			$remoteUser['id'],
			$remoteUser['roles'],
			[
				'name' => $remoteUser['fullname'],
				'email' => $remoteUser['email'],
			]
		);
	}
}
