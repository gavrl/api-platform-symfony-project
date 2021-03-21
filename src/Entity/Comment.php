<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CommentRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
#[
    ApiResource(
        collectionOperations: [
            'get',
            'post' => [
                'access_control' => "is_granted('ROLE_COMMENTATOR')"
            ]
        ],
        itemOperations: [
            'get',
            'put' => [
                'access_control' => "is_granted('ROLE_EDITOR') or (is_granted('ROLE_COMMENTATOR') and object.getAuthor() == user)"
            ]
        ],
        subresourceOperations: [
            'api_blog_posts_comments_get_subresource' => [
                'method' => 'GET',
                'normalization_context' => [
                    'groups' => ['get-comment-with-author']
                ]
            ]
        ],
        attributes: [
            'order' => ['published' => 'DESC'],
            'pagination_client_enabled' => true
        ],
        denormalizationContext: [
            'groups' => ['post']
        ]
    )
]
class Comment implements AuthoredEntityInterface, PublishedDateEntityInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['get-comment-with-author'])]
    private ?int $id;

    /**
     * @ORM\Column(type="text")
     */
    #[
        Assert\NotBlank,
        Assert\Length(min: 5, max: 3000),
        Groups(['post', 'get-comment-with-author'])
    ]
    private ?string $content;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['get-comment-with-author'])]
    private ?DateTimeInterface $published;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['get-comment-with-author'])]
    private UserInterface $author;

    /**
     * @ORM\ManyToOne(targetEntity="BlogPost", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['post'])]
    private BlogPost $post;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPublished(): ?DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(DateTimeInterface $published): self
    {
        $this->published = $published;

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

    /**
     * @return BlogPost
     */
    public function getPost(): BlogPost
    {
        return $this->post;
    }

    /**
     * @param BlogPost $post
     * @return $this
     */
    public function setPost(BlogPost $post): self
    {
        $this->post = $post;

        return $this;
    }
}
