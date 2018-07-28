<?php

namespace App\EventSubscriber;

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
        // $entity = $args->getObject();
    }
}