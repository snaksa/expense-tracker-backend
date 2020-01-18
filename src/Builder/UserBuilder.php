<?php declare (strict_types=1);

namespace App\Builder;

use App\Entity\User;
use App\Exception\PasswordConfirmationException;
use App\GraphQL\Input\User\UserRequest;
use App\Traits\DateUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserBuilder extends BaseBuilder
{
    use DateUtils;

    /**
     * @var User
     */
    private $user;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct($entityManager);
    }

    public function create(): self
    {
        $this->user = new User();

        return $this;
    }

    /**
     * @param UserRequest $input
     * @return UserBuilder
     * @throws EntityNotFoundException
     */
    public function bind(UserRequest $input): self
    {
        if ($input->id !== null) {
            $this->setUser($this->findEntity($input->id, User::class));
        }

        if ($input->email !== null) {
            $this->withEmail($input->email);
        }

        if ($input->password !== null) {
            if ($input->password !== $input->confirmPassword) {
                throw new PasswordConfirmationException();
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

    public function withPassword(string $password): self
    {
        $password = $this->passwordEncoder->encodePassword($this->user, $password);
        $this->user->setPassword($password);

        return $this;
    }

    public function withApiKey(): self
    {
        $apiKey = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $expiryDate = $this->getCurrentDateTime()->modify('+ 3 hours');

        $this->user->setApiKey($apiKey);
        $this->user->setApiKeyExpiryDate($expiryDate);

        return $this;
    }

    public function build(): User
    {
        return $this->user;
    }
}
