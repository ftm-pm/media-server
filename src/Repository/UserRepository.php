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
     * @return mixed|null|\Symfony\Component\Security\Core\User\UserInterface
     */
    public function loadUserByUsername($email, $username = null)
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