<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;

    /**
     * UserFixtures constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $user = new User();

        $user
            ->setPseudo($faker->userName)
            ->setEmail("used@email.com")
            ->setPassword(
                $this->encoder->encodePassword($user, 'password')
            )
            ->setFirstname($faker->firstName)
            ->setLastname($faker->lastName)
            ->setIsVerified(true)
        ;

        $manager->persist($user);
        $manager->flush();
    }
}
