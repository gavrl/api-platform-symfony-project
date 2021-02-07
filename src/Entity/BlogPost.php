<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BlogPostRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BlogPostRepository::class)
 * @ApiResource(
 *     itemOperations={
 *         "get",
 *         "put"={
 *             "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object.getAuthor() == user"
 *         }
 *     },
 *     collectionOperations={
 *         "get",
 *         "post"={
 *             "accessControl"="is_granted('IS_AUTHENTICATED_FULLY')"
 *         }
 *     }
 * )
 */
class BlogPost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=10)
     */
    private ?string $title;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Type("DateTimeInterface")
     */
    private ?DateTimeInterface $published;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Length(min=20)
     */
    private ?string $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $author;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post")
     */
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

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

    public function getPublished(): ?DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(DateTimeInterface $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @param User $author
     * @return $this
     */
    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }
}
