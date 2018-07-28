<?php

namespace App\EntityListener;

use App\Entity\User;
use App\Handler\UserHandler;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class UserListener
 * @package App\EntityListener
 */
class UserListener
{
    /**
     * @var UserHandler
     */
    private $userHandler;

    /**
     * UserListener constructor.
     * @param UserHandler $userHandler
     */
    public function __construct(UserHandler $userHandler)
    {
        $this->userHandler = $userHandler;
    }

    /**
     * @param User $user
     * @param LifecycleEventArgs $args
     * @throws \Exception
     */
    public function prePersist(User $user, LifecycleEventArgs $args)
    {
        $this->userHandler->sendActivateMessage($user);
        $this->userHandler->hashPassword($user);
    }

    /**
     * @param User $user
     * @param PreUpdateEventArgs $args
     * @throws \Exception
     */
    public function preUpdate(User $user, PreUpdateEventArgs $args)
    {
        $this->update($user, $args);
        $this->userHandler->hashPassword($user);
    }

    /**
     * @param User $user
     * @param PreUpdateEventArgs $args
     */
    private function update(User $user, PreUpdateEventArgs $args) {
        if($args->hasChangedField('username')) {
            $user->setUsername($args->getOldValue('username'));
        }
    }
}