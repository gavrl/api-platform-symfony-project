<?php

namespace App\Entity;

use JetBrains\PhpStorm\Pure;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\BlogPostRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=BlogPostRepository::class)
 */
#[
    ApiResource(
        collectionOperations: [
            'get',
            'post' => [
                'access_control' => "is_granted('ROLE_WRITER')"
            ]
        ],
        itemOperations: [
            'get' => [
                'normalization_context' => [
                    'groups' => ['get-blog-post-with-author']
                ]
            ],
            'put' => [
                'access_control' => "is_granted('ROLE_EDITOR') or (is_granted('ROLE_WRITER') and object.getAuthor() == user)"
            ]
        ],
        attributes: [
            'order' => ['published' => 'DESC'],
            'maximum_items_per_page' => 30,
            'pagination_partial' => true
        ],
        denormalizationContext: [
            'groups' => ['post']
        ]
    ),
    ApiFilter(
        SearchFilter::class,
        properties: [
            'id' => 'exact',
            'title' => 'partial',
            'content' => 'partial',
            'author' => 'exact',
            'author.name' => 'partial'
        ]
    ),
    ApiFilter(
        DateFilter::class,
        properties: [
            'published'
        ]
    ),
    ApiFilter(
        RangeFilter::class,
        properties: ['id']
    ),
    ApiFilter(
        OrderFilter::class,
        properties: [
            'id',
            'published',
            'title'
        ],
        arguments: ['orderParameterName' => '_order']
    ),
    ApiFilter(
        PropertyFilter::class,
        arguments: [
            'parameterName' => 'properties',
            'overrideDefaultProperties' => false,
            'whitelist' => [
                'id', 'author', 'slug', 'title', 'content'
            ]
        ]
    )
]
class BlogPost implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['get-blog-post-with-author'])]
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Assert\NotBlank,
        Assert\Length(min: 10),
        Groups(['post', 'get-blog-post-with-author'])
    ]
    private ?string $title;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['get-blog-post-with-author'])]
    private ?DateTimeInterface $published;

    /**
     * @ORM\Column(type="text")
     */
    #[
        Assert\NotBlank,
        Assert\Length(min: 20),
        Groups(['post', 'get-blog-post-with-author'])
    ]
    private ?string $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[
        Assert\NotBlank,
        Groups(['post', 'get-blog-post-with-author'])
    ]
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['get-blog-post-with-author'])]
    private UserInterface $author;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post")
     */
    #[
        ApiSubresource,
        Groups(['get-blog-post-with-author'])
    ]
    private $comments;


    /**
     * @ORM\ManyToMany(targetEntity="Image")
     * @ORM\JoinTable()
     */
    #[
        ApiSubresource(),
        Groups(['post', 'get-blog-post-with-author'])
    ]
    private $images;

    #[Pure]
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
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
     * @param UserInterface $author
     * @return $this
     */
    public function setAuthor(UserInterface $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @return ArrayCollection
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image)
    {
        $this->images->add($image);
    }

    public function removeImage(Image $image)
    {
        $this->images->removeElement($image);
    }
}
