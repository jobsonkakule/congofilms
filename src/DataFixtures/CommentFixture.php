<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 10; $i++) {
            $comment = new Comment();
            $comment
                ->setContent($faker->words(8, true))
                ->setParentId(0)
                ->setAuthor('Job Kakule')
                ->setEmail('jobkakule10@gmail.com')
                ;
            $manager->persist($comment);
        }
        $manager->flush();
    }
}
