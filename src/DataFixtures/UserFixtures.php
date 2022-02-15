<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    const NAMES = [
        'Xavier',
        'Olivier',
        'Guillaume',
        'Anthony',
        'Thomas',
        'Damien',
        'Greg',
        'Aurelien',
        'Seb',
        'Eleonore',
        'Claire',
        'Celia',
        'Karine',
        'Stéphanie',
        'Joffrey',
        'Jonathan',
        'Charles',
        'Enzo',
        'Mathis',
        'Iliess',
        'Brice',
        'Alexandre',
    ];

    const MAILS = [
        'live',
        'hotmail',
        'gmail',
        'yahoo',
    ];

    const ENDS = [
        'fr',
        'com'
    ];
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        foreach(self::NAMES as $name){
            $user = new User();
            $user->setEmail($name . '@' . self::MAILS[array_rand(self::MAILS)] . '.' . self::ENDS[array_rand(self::ENDS)]);
            $user->setPassword(uniqid());
            $user->setScore(rand(0, 100));
            $manager->persist($user);
        }
        $user->setEmail('ali@test.com');
        $user->setPassword('teeeest');
        $user->setScore(rand(0, 100));
        $manager->persist($user);
        $manager->flush();
    }
}
