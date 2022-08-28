<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\ApiPlatform\Dto\LoginInput;
use App\ApiPlatform\Dto\ResetPassTokenInput;
use App\Controller\GetCurrentUser;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    collectionOperations: [
        'login' => [
            'method' => 'POST',
            'status' => Response::HTTP_OK,
            'path' => '/user/login',
            'input' => LoginInput::class,
            'openapi_context' => [
                'summary' => 'Request an authentication token using email/password',
                'description' => 'Request an authentication token using email/password',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Authenticated successfully',
                    ],
                    Response::HTTP_UNAUTHORIZED => [
                        'description' => 'Wrong credentials',
                    ],
                ],
            ],
        ],
        'reset_pass_token_consume' => [
            'method' => 'POST',
            'status' => Response::HTTP_OK,
            'path' => '/user/reset/consume',
            'input' => ResetPassTokenInput::class,
            'openapi_context' => [
                'summary' => 'Request an authentication token using a reset password token',
                'description' => 'Request an authentication token using a reset password token',
                'responses' => [
                    Response::HTTP_OK => [
                        'description' => 'Authenticated successfully',
                    ],
                    Response::HTTP_UNAUTHORIZED => [
                        'description' => 'Wrong credentials',
                    ],
                ],
            ],
        ],
    ],
    itemOperations: [
        'get' => [
            'path' => '/user/me',
            'controller' => GetCurrentUser::class,
            'openapi_context' => [
                'summary' => 'Get current user information',
                'description' => '#withoutIdentifier Get current user information',
                'parameters' => [],
            ],
            'read' => false,
        ],
        'put' => [
            'path' => '/user/{id}',
            'security' => 'object == user',
        ],
    ],
    denormalizationContext: ['groups' => ['user:input']],
    normalizationContext: ['groups' => ['user:output']],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    #[Groups(['user:output'])]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private string $identifier = '';

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:output', 'register:input'])]
    private string $email = '';

    /**
     * @var string[]
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private string $password = '';

    #[SerializedName('password')]
    #[Groups(['user:input'])]
    private ?string $plainPassword = null;

    /**
     * @var Collection<int, AlnFeeder>
     */
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: AlnFeeder::class)]
    #[Groups(['user:output'])]
    private Collection $feeders;

    public function __construct()
    {
        $this->feeders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getEmail(): string
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
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUnsubscribeToken(): string
    {
        $payload = implode('-', [$this->getId(), $this->getIdentifier()]);

        return strtr(base64_encode($payload), ['+' => '-', '/' => '_', '=' => '']);
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, AlnFeeder>
     */
    public function getFeeders(): Collection
    {
        return $this->feeders;
    }

    public function addFeeder(AlnFeeder $feeder): self
    {
        if (!$this->feeders->contains($feeder)) {
            $this->feeders[] = $feeder;
            $feeder->setOwner($this);
        }

        return $this;
    }

    public function removeFeeder(AlnFeeder $feeder): self
    {
        if ($this->feeders->removeElement($feeder)) {
            // set the owning side to null (unless already changed)
            if ($feeder->getOwner() === $this) {
                $feeder->setOwner(null);
            }
        }

        return $this;
    }
}
