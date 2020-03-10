<?php declare (strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Exception\InvalidPasswordException;
use App\Exception\NotAuthenticatedException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorizationService
{

    /**
     * @var Security $security
     */
    private $security;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @param Security $security
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(Security $security, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->security = $security;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function isLoggedIn(): bool
    {
        try {
            return $this->getCurrentUser()->getId() !== '';
        } catch (NotAuthenticatedException $e) {
            return false;
        }
    }

    /**
     * @return User
     * @throws NotAuthenticatedException
     */
    public function getCurrentUser(): User
    {
        if ($this->security->getToken() === null) {
            throw new NotAuthenticatedException();
        }

        $user = $this->security->getToken()->getUser();

        if ($user instanceof User) {
            return $user;
        }

        throw new NotAuthenticatedException();
    }

    public function isPasswordValid(?UserInterface $user, string $password)
    {
        if (!$user || !$this->passwordEncoder->isPasswordValid($user, $password)) {
            throw new InvalidPasswordException();
        }
    }
}
