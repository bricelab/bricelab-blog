<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class TagFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;

    /**
     * TagFixtures constructor.
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $nbTags = mt_rand(10,50);
//        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'used@email.com']);
        for($cpt=0; $cpt  < $nbTags; $cpt++){
            $tag = new Tag();
            $name = ucfirst($faker->word);
            $tag
                ->setName($name)
                ->setSlug(
                    $this->slugger->slug(strtolower($name))
                )
            ;
            $manager->persist($tag);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
