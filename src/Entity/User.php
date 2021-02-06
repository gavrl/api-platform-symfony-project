<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ApiResource(
 *     itemOperations={"get"},
 *     collectionOperations={"post"},
 *     normalizationContext={
 *          "groups"={"read"}
 *     }
 * )
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private ?int $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=30, unique=true)
     *
     * @Groups({"read"})
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=30)
     */
    private $username;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50)
     *
     * @Groups({"read"})
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=5, max=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Assert\Length(min=6, max=180)
     */
    private ?string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
     *     message="Password should be 7 characters logn and contain at least one digit, one upper case letter and one lower case letter"
     * )
     */
    private string $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Expression(
     *     "this.getPassword() === this.getRetypedPassword()",
     *     message="Password does not match"
     * )
     */
    private $retypedPassword;

    /**
     * @ORM\OneToMany(targetEntity="BlogPost", mappedBy="author")
     * @Groups({"read"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="author")
     * @Groups({"read"})
     */
    private $comments;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
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
     * @return string
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
}
