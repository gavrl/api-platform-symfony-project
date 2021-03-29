<?php

namespace App\Entity;

use JetBrains\PhpStorm\Pure;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\ResetPasswordAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
#[
    UniqueEntity('username'),
    UniqueEntity('email'),
    ApiResource(
        collectionOperations: [
            'post' => [
                'denormalization_context' => [
                    'groups' => ['post'],
                ],
                'normalization_context' => [
                    'groups' => ['get'],
                ],
                'validation_groups' => ['post']
            ]
        ],
        itemOperations: [
            'get' => [
                'access_control' => "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
                'normalization_context' => [
                    'groups' => ['get']
                ]
            ],
            'put' => [
                'access_control' => "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
                'denormalization_context' => [
                    'groups' => ['put']
                ],
                'normalization_context' => [
                    'groups' => ['get']
                ]
            ],
            'put-reset-password' => [
                'access_control' => "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
                'method' => 'PUT',
                'path' => '/users/{id}/reset-password',
                'controller' => ResetPasswordAction::class,
                'denormalization_context' => [
                    'groups' => ['put-reset-password']
                ],
                'validation_groups' => ['put-reset-password']
            ]
        ]
    )
]
class User implements UserInterface
{
    const ROLE_COMMENTATOR = 'ROLE_COMMENTATOR';
    const ROLE_WRITER      = 'ROLE_WRITER';
    const ROLE_EDITOR      = 'ROLE_EDITOR';
    const ROLE_ADMIN       = 'ROLE_ADMIN';
    const ROLE_SUPERADMIN  = 'ROLE_SUPERADMIN';

    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['get'])]
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     */
    #[
        Groups(['get', 'post', 'get-comment-with-author', 'get-blog-post-with-author']),
        Assert\NotBlank(['groups' => ['post']]),
        Assert\Length(min:5, max:30, groups:['post'])
    ]
    private ?string $username;

    /**
     * @ORM\Column(type="string", length=50)
     */
    #[
        Groups(["get", "post", "put", "get-comment-with-author", "get-blog-post-with-author"]),
        Assert\NotBlank(['groups' => ['post']]),
        Assert\Length(min:5, max:30, groups:['post', 'put'])
    ]
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    #[
        Groups(['post', 'put', 'get-admin', 'get-owner']),
        Assert\NotBlank(['groups' => ['post']]),
        Assert\Email(['groups' => ['post', 'put']]),
        Assert\Length(min:6, max:180, groups: ['post', 'put'])
    ]
    private ?string $email;

    /**
     * @ORM\Column(type="simple_array", length=200)
     */
    #[Groups(['get-admin', 'get-owner'])]
    private array $roles;

    /**
     * @ORM\Column(type="string")
     */
    #[
        Groups(['post']),
        Assert\NotBlank(['groups' => ['post']]),
        Assert\Regex(
            pattern: '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/',
            message: 'Password should be 7 characters long and contain at least one digit, one upper case letter and one lower case letter',
            groups: ['post']
        )
    ]
    private $password;

    #[
        Groups(['post']),
        Assert\NotBlank(['groups' => ['post']]),
        Assert\Expression(
            expression: 'this.getPassword() === this.getRetypedPassword()',
            message: 'Password does not match',
            groups: ['post']
        )
    ]
    private $retypedPassword;

    #[
        Groups(['put-reset-password']),
        Assert\NotBlank(groups: ['put-reset-password']),
        Assert\Regex(
            pattern: '/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/',
            message: 'Password must be seven characters long and contain at least one digit, one upper case letter and one lower case letter',
            groups: ['put-reset-password']
        )
    ]
    private ?string $newPassword;

    #[
        Groups(['put-reset-password']),
        Assert\NotBlank(['groups' => ['put-reset-password']]),
        Assert\Expression(
            expression: 'this.getNewPassword() === this.getNewRetypedPassword()',
            message: 'Passwords does not match',
            groups: ['put-reset-password']
        )
    ]
    private ?string $newRetypedPassword;

    #[
        Groups(['put-reset-password']),
        Assert\NotBlank(groups: ['put-reset-password']),
        UserPassword(groups: ['put-reset-password'])
    ]
    private ?string $oldPassword;

    /**
     * @ORM\OneToMany(targetEntity="BlogPost", mappedBy="author")
     */
    #[Groups(['get'])]
    private Collection $posts;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="author")
     */
    #[Groups(['get'])]
    private Collection $comments;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $passwordChangeDate = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $enabled;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private ?string $confirmationToken;

    /**
     * User constructor.
     */
    #[Pure] public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
        $this->enabled = false;
        $this->confirmationToken = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $username
     * @return $this
     */
    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
//         guarantee every user at least has ROLE_USER
//        $roles[] = 'ROLE_USER';
//
//        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name): User
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRetypedPassword(): ?string
    {
        return $this->retypedPassword;
    }

    /**
     * @param string|null $retypedPassword
     * @return User
     */
    public function setRetypedPassword(?string $retypedPassword): User
    {
        $this->retypedPassword = $retypedPassword;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getUsername();
    }

    /**
     * @return string|null
     */
    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    /**
     * @param string|null $newPassword
     * @return User
     */
    public function setNewPassword(?string $newPassword): User
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewRetypedPassword(): ?string
    {
        return $this->newRetypedPassword;
    }

    /**
     * @param string|null $newRetypedPassword
     * @return User
     */
    public function setNewRetypedPassword(?string $newRetypedPassword): User
    {
        $this->newRetypedPassword = $newRetypedPassword;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    /**
     * @param string|null $oldPassword
     * @return User
     */
    public function setOldPassword(?string $oldPassword): User
    {
        $this->oldPassword = $oldPassword;
        return $this;
    }

    /**
     * @return ?int
     */
    public function getPasswordChangeDate(): ?int
    {
        return $this->passwordChangeDate;
    }

    /**
     * @param int|null $passwordChangeDate
     * @return $this
     */
    public function setPasswordChangeDate(?int $passwordChangeDate): self
    {
        $this->passwordChangeDate = $passwordChangeDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return User
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * @param ?string $confirmationToken
     * @return User
     */
    public function setConfirmationToken(?string $confirmationToken): User
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }
}
