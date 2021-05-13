<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;

    /**
     * PostFixtures constructor.
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'used@email.com']);
        $categories = $manager->getRepository(Category::class)->findBy([]);
        $tags = $manager->getRepository(Tag::class)->findBy([]);

        $nbPosts = mt_rand(50, 250);

        for($cpt=0; $cpt  < $nbPosts; $cpt++){
            $post = new Post();

            $title = $faker->words(mt_rand(3, 5), true);
            $summary = $faker->sentence(nbWords: 40);

            $post
                ->setTitle(ucfirst($title))
                ->setSlug(
                    $this->slugger->slug($title)
                )
                ->setSummary($summary)
                ->setContent($faker->paragraph(mt_rand(50, 250)))
                ->setFeaturedImage($faker->imageUrl(height: 300))
                ->setAuthor($user)
                ->setPublishedAt($faker->dateTimeBetween('-3 months', '+1 day'))
            ;

            /** @var Category[] $postCategories */
            $postCategories = $faker->randomElements($categories, mt_rand(1, count($categories)));

            foreach($postCategories as $category){
                $post->addCategory($category);
            }

            /** @var Tag[] $postCategories */
            $postTags = $faker->randomElements($tags, mt_rand(3, count($tags)));

            foreach($postTags as $tag){
                $post->addTag($tag);
            }

            $manager->persist($post);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            UserFixtures::class,
            TagFixtures::class,
        ];
    }
}
