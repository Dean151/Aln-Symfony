<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\AlnFeeder;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<'VIEW'|'MANAGE', AlnFeeder>
 */
final class FeederVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const MANAGE = 'MANAGE';

    public function __construct(
        #[Autowire('%env(bool:AUTHENTICATION_ENABLED)%')]
        private readonly bool $authenticationEnabled,
    ) {
    }

    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, [self::VIEW, self::MANAGE]);
    }

    public function supportsType(string $subjectType): bool
    {
        return AlnFeeder::class === $subjectType;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsType(get_debug_type($subject)) && $this->supportsAttribute($attribute);
    }

    /**
     * @param AlnFeeder $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$this->authenticationEnabled) {
            return true;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        if ($subject->getOwner() !== $user) {
            return false;
        }

        return true;
    }
}
