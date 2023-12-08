<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\ApiResource\Dto\LoginInput;
use App\ApiResource\Dto\ResetPassTokenInput;
use App\ApiResource\Processor\UserProcessor;
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
    operations: [
        new Get(
            uriTemplate: '/user/me',
            controller: GetCurrentUser::class,
            openapiContext: [
                'summary' => 'Get current user information',
                'description' => '#withoutIdentifier Get current user information',
                'parameters' => [],
            ],
            read: false
        ),
        new Put(
            uriTemplate: '/user/{id}',
            security: 'object == user',
            processor: UserProcessor::class,
        ),
        new Post(
            uriTemplate: '/user/login',
            status: Response::HTTP_OK,
            openapiContext: [
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
            input: LoginInput::class,
        ),
        new Post(
            uriTemplate: '/user/reset/consume',
            status: Response::HTTP_OK,
            openapiContext: [
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
            input: ResetPassTokenInput::class,
        ),
    ],
    normalizationContext: ['groups' => ['user:output']],
    denormalizationContext: ['groups' => ['user:input']]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
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
