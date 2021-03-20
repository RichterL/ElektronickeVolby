<?php

namespace Models;

use Dibi\Connection;
use Nette\Database\Explorer;
use Repositories\UserRepository;

class AclModel extends BaseModel
{
    private $userRepository;
    protected $database;
    protected $dibi;

    public function __construct(UserRepository $userRepository, Explorer $database, Connection $dibi)
    {
        $this->userRepository = $userRepository;
        $this->database = $database;
        $this->dibi = $dibi;
    }

    public function getRoles()
    {
        return $this->database->table(Tables::ACL_ROLES)->fetchAll();
    }

    public function getResources()
    {
        return $this->database->table(Tables::ACL_RESOURCES)->fetchAll();
    }

    public function getRules()
    {
        return $this->database->table(Tables::ACL_RULES)->fetchAll();
    }

    public function getUsersDatasource()
    {
        $users = $this->dibi->select('u.*, r.name as role')->from('%n u', Tables::USERS)
            ->leftJoin('%n ur', Tables::USERS_ROLES)->on('u.id = ur.user_id')
            ->leftJoin('%n r', Tables::ACL_ROLES)->on('r.id = ur.role_id');

        return $users;
    }

    public function getUserByUsername(string $username): ?Entities\User
    {
        $user = $this->userRepository->findByUsername($username);
        return $user;
    }

    public function getUser(int $id)
    {
        $res = $this->dibi->select('*')->from(Tables::USERS)->where('id = %i', $id)->fetchSingle();
        // $user = new User
        // $roles =
    }

    public function getUsers()
    {
        $select = $this->dibi->select('u.*, r.name as roles')->from('%n u', Tables::USERS)
        ->join('%n ur', Tables::USERS_ROLES)->on('u.id = ur.user_id')
        ->join('%n r', Tables::ACL_ROLES)->on('r.id = ur.role_id');

        $arr1 = $select->fetchAssoc('id[]roles=roles');
        $arr2 = $select->fetchAssoc('id->roles[]=roles');
    }
}
