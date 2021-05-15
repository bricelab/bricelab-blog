<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;

    /**
     * CategoryFixtures constructor.
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $nbCategories = mt_rand(5,10);
//        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'used@email.com']);
        for($cpt=0; $cpt  < $nbCategories; $cpt++){
            $category = new Category();
            $name = ucfirst($faker->word) . ' '. $faker->word;
            $category
                ->setName($name)
                ->setSlug(
                    $this->slugger->slug(strtolower($name))
                )
            ;
            $manager->persist($category);
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
