<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Exception\UserExistsException;
use App\Handler\UserHandler;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class DoctrineEventSubscriber
 * @package App\EventSubscriber
 */
class DoctrineEventSubscriber implements EventSubscriber
{
    /**
     * @var UserHandler
     */
    private $userHandler;

    /**
     * DoctrineEventSubscriber constructor.
     * @param UserHandler $userHandler
     */
    public function __construct(UserHandler $userHandler)
    {
        $this->userHandler = $userHandler;
    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->update($args);

        $entity = $args->getObject();
        if ($entity instanceof User) {
           $this->userHandler->sendActivateMessage($entity);
        }
    }

    public function sendMail(LifecycleEventArgs $args)
    {
        $obm = $args->getObjectManager();
        $rep = $obm->getRepository('App:User');
        $user = $args->getObject();
        $userExists = $rep->loadUserByUsername($user->getEmail(), $user->getUsername());

        if($userExists) {
            throw new UserExistsException('User exist.');
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->update($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function update(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof User) {
            $this->userHandler->hashPassword($entity);
        }
    }
}