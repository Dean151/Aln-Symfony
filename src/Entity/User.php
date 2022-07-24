<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\ApiPlatform\Dto\LoginInput;
use App\Controller\GetCurrentUserController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

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
    ],
    itemOperations: [
        'get' => [
            'path' => 'user/me',
            'controller' => GetCurrentUserController::class,
            'openapi_context' => [
                'summary' => 'Get current user information',
                'description' => '#withoutIdentifier Get current user information',
                'parameters' => [],
            ],
            'read' => false,
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

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:input', 'user:output'])]
    private string $email = '';

    /**
     * @var string[]
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['user:input'])]
    private string $password = '';

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
        // If you store any temporary, sensitive data on the user, clear it here
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
