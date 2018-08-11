<?php

namespace App\EntityListener;

use App\Entity\Document;
use App\Handler\DocumentHandler;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * Class DocumentListener
 * @package App\EntityListener
 */
class DocumentListener
{
    /**
     * @var DocumentHandler
     */
    private $documentHandler;

    /**
     * DocumentListener constructor.
     * @param DocumentHandler $documentHandler
     */
    public function __construct(DocumentHandler $documentHandler)
    {
        $this->documentHandler = $documentHandler;
    }

    /**
     * @param Document $document
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Document $document, LifecycleEventArgs $args)
    {
        $this->documentHandler->setOwner($document);
    }

    /**
     * @param Document $document
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Document $document, PreUpdateEventArgs $args)
    {
    }
}