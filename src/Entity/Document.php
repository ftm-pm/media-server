<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Class Document
 * @package App\Entity
 *
 * @ORM\Table(name="documents")
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks
 */
class Document
{
    use TimestampableTrait;

    /**
     * @var int $id The entity Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="document", fileNameProperty="documentName", size="documentSize")
     *
     * @var File $documentFile
     */
    private $documentFile;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @var string $documentName
     */
    private $documentName;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer $documentSize
     */
    private $documentSize;

    /////////////////////////////////

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param null|File|\Symfony\Component\HttpFoundation\File\UploadedFile $documentFile
     * @throws \Exception
     */
    public function setDocumentFile(?File $documentFile = null): void
    {
        $this->documentFile = $documentFile;

        if (null !== $documentFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return null|File
     */
    public function getDocumentFile(): ?File
    {
        return $this->documentFile;
    }

    /**
     * @param null|string $documentName
     */
    public function setDocumentName(?string $documentName): void
    {
        $this->documentName = $documentName;
    }

    /**
     * @return null|string
     */
    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    /**
     * @param int|null $documentSize
     */
    public function setDocumentSize(?int $documentSize): void
    {
        $this->documentSize = $documentSize;
    }

    /**
     * @return int|null
     */
    public function getDocumentSize(): ?int
    {
        return $this->documentSize;
    }
}