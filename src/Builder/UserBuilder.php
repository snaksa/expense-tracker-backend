<?php declare (strict_types=1);

namespace App\Builder;

use App\Entity\User;
use App\Exception\PasswordConfirmationException;
use App\Exception\UserAlreadyExistsException;
use App\GraphQL\Input\User\UserRequest;
use App\Repository\UserRepository;
use App\Traits\DateUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserBuilder extends BaseBuilder
{
    use DateUtils;

    /**
     * @var User
     */
    private $user;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        JWTTokenManagerInterface $jwtManager
    ) {
        $this->userRepository = $userRepository;
        $this->passwordEncoder = $passwordEncoder;
        $this->jwtManager = $jwtManager;

        parent::__construct($entityManager);
    }

    public function create(): self
    {
        $this->user = new User();
        $this->user->setRoles($this->user->getRoles());

        return $this;
    }

    /**
     * @param UserRequest $input
     * @return UserBuilder
     * @throws EntityNotFoundException
     * @throws UserAlreadyExistsException
     */
    public function bind(UserRequest $input): self
    {
        if ($input->id !== null) {
            $this->setUser($this->findEntity($input->id, User::class));
        }

        if ($input->email !== null) {
            $user = $this->userRepository->findOneBy(['email' => $input->email]);
            if ($user && $user->getId() !== $this->user->getId()) {
                throw new UserAlreadyExistsException('User with this email already exists');
            }

            $this->withEmail(trim($input->email));
        }

        if ($input->firstName !== null) {
            $this->withFirstName($input->firstName);
        }

        if ($input->lastName !== null) {
            $this->withLastName($input->lastName);
        }

        if ($input->currency !== null) {
            $this->withCurrency($input->currency);
        }

        if ($input->language !== null) {
            $this->withLanguage($input->language);
        }

        if ($input->password !== null) {
            if ($input->password !== $input->confirmPassword) {
                throw new PasswordConfirmationException('Passwords do not match');
            }

            $this->withPassword($input->password);
        }

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->user->setEmail($email);

        return $this;
    }

    public function withFirstName(string $firstName): self
    {
        $this->user->setFirstName($firstName);

        return $this;
    }

    public function withLastName(string $lastName): self
    {
        $this->user->setLastName($lastName);

        return $this;
    }

    public function withCurrency(string $currency): self
    {
        $this->user->setCurrency($currency);

        return $this;
    }

    public function withLanguage(string $language): self
    {
        $this->user->setLanguage($language);

        return $this;
    }

    public function withPassword(string $password): self
    {
        $password = $this->passwordEncoder->encodePassword($this->user, $password);
        $this->user->setPassword($password);

        return $this;
    }

    public function withApiKey(): string
    {
        $this->jwtManager->setUserIdentityField('email');
        return $this->jwtManager->create($this->user);
    }

    public function build(): User
    {
        return $this->user;
    }
}
