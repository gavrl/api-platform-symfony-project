<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Controller\UploadImageAction;
use ApiPlatform\Core\Annotation\ApiResource;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Class Image
 * @package App\Entity
 *
 * @ORM\Entity()
 * @Vich\Uploadable()
 */
#[ApiResource(
    collectionOperations: [
        'get',
        'post' => [
            'method' => 'POST',
            'path' => '/images',
            'controller' => UploadImageAction::class,
            'defaults' => [
                '_api_receive' => false
            ]
        ]
    ]
)]
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(nullable=true)
     */
    private string $url;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="url")
     */
    private $file;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Image
    {
        $this->url = $url;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }
}