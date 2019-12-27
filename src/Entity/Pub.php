<?php

namespace App\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PubRepository")
 * @UniqueEntity("title")
 * @Vich\Uploadable
 */
class Pub
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=5, max=100)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $smallfile;

    /**
     * @Vich\UploadableField(mapping="pub_image", fileNameProperty="smallfile")
     * @var File|null
     * @Assert\Image(mimeTypes={"image/jpeg", "image/png"}, maxSize="10M")
     */
    private $imageSmallfile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $largefile;

    /**
     * @Vich\UploadableField(mapping="pub_image", fileNameProperty="largefile")
     * @var File|null
     * @Assert\Image(mimeTypes={"image/jpeg", "image/png"}, maxSize="10M")
     */
    private $imageLargefile;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $promo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_at;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $link;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSmallfile(): ?string
    {
        return $this->smallfile;
    }

    public function setSmallfile(?string $smallfile): self
    {
        $this->smallfile = $smallfile;

        return $this;
    }

    /**
     * Get the value of imageFile
     *
     * @return  File|null
     */ 
    public function getImageSmallfile()
    {
        return $this->imageSmallfile;
    }

    /**
     * Set the value of imageFile
     *
     * @param  File|null  $imageFile
     *
     * @return  self
     */ 
    public function setImageSmallfile($imageSmallfile)
    {
        $this->imageSmallfile = $imageSmallfile;
        if ($this->imageSmallfile instanceof UploadedFile) {
            $this->updated_at = new \DateTime('now');
        }

        return $this;
    }

    public function getLargefile(): ?string
    {
        return $this->largefile;
    }

    public function setLargefile(?string $largefile): self
    {
        $this->largefile = $largefile;

        return $this;
    }

    /**
     * Get the value of imageFile
     *
     * @return  File|null
     */ 
    public function getImageLargefile()
    {
        return $this->imageLargefile;
    }

    /**
     * Set the value of imageFile
     *
     * @param  File|null  $imageFile
     *
     * @return  self
     */ 
    public function setImageLargefile($imageLargefile)
    {
        $this->imageLargefile = $imageLargefile;
        if ($this->imageLargefile instanceof UploadedFile) {
            $this->updated_at = new \DateTime('now');
        }

        return $this;
    }

    public function getPromo(): ?bool
    {
        return $this->promo;
    }

    public function setPromo(bool $promo): self
    {
        $this->promo = $promo;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }
}
