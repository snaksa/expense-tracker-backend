<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('demo@gmail.com');
        $user->setFirstName('Demo');
        $user->setLastName('User');
        $user->setRoles(['ROLE_USER']);
        $password = $this->passwordEncoder
            ->encodePassword($user, '123456');
        $user->setPassword($password);
        $manager->persist($user);
        $this->setReference('user_demo', $user);

        $user = new User();
        $user->setEmail('demo2@gmail.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setRoles(['ROLE_USER']);
        $password = $this->passwordEncoder
            ->encodePassword($user, '123456');
        $user->setPassword($password);
        $manager->persist($user);
        $this->setReference('user_demo2', $user);

        $manager->flush();
    }
}
