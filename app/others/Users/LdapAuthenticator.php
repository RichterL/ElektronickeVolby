<?php

use LDAP\LdapException;
use LDAP\Service;
use App\Models\Entities\User;
use Nette\Security\IIdentity;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;

class LdapAuthenticator implements Nette\Security\Authenticator
{
	private $userRepository;
	private $roleRepository;
	private $ldapService;

	public function __construct(UserRepository $userRepository, RoleRepository $roleRepository, Service $service)
	{
		$this->userRepository = $userRepository;
		$this->roleRepository = $roleRepository;
		$this->ldapService = $service;
	}

	public function authenticate(string $username, string $password): IIdentity
	{
		try {
			$remoteUser = $this->ldapService->getRemoteUser($username, $password);
		} catch (LdapException $ex) {
			throw new Nette\Security\AuthenticationException('Authentication failed.');
		}

		$user = $this->userRepository->findByUsername($remoteUser['email']);
		if ($user) {
			return $user;
		}
		$user = new User();

		$roles = $this->roleRepository->findByKey(reset($remoteUser['roles']));
		$user->setEmail($remoteUser['email'])->setName($remoteUser['fullname'])->setUsername($remoteUser['email'])->setRoles(...$roles);
		return $user;
	}
}
