<?php

namespace App\EntityListener;

use App\Entity\Image;
use App\Handler\ImageHandler;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class ImageListener
 * @package App\EntityListener
 */
class ImageListener
{
    /**
     * @var ImageHandler
     */
    private $imageHandler;

    /**
     * ImageListener constructor.
     * @param ImageHandler $imageHandler
     */
    public function __construct(ImageHandler $imageHandler)
    {
        $this->imageHandler = $imageHandler;
    }

    /**
     * @param Image $image
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Image $image, LifecycleEventArgs $args)
    {
        $this->imageHandler->setOwner($image);
    }

    /**
     * @param Image $image
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Image $image, PreUpdateEventArgs $args)
    {
    }
}