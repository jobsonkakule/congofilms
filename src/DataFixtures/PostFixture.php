<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class PostFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 10; $i++) {
            $post = new Post();
            $post
                ->setTitle($faker->words(8, true))
                ->setContent($faker->sentences(50, true))
                ->setOnline(true)
                ->setViews(0)
                ;
            $manager->persist($post);
        }
        $manager->flush();
    }
}
