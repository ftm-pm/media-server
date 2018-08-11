<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * @param string $email
     * @param null $username
     * @return User|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByUsername($email, $username = null): ?User
    {
        if(!$username) {
            $username = $email;
        }

        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
}