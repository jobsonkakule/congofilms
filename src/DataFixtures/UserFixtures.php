<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('jobson');
        $user->setEmail('jobkakule10@gmail.com');
        $user->setPassword($this->encoder->encodePassword($user, 'jobson'));
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
        $user->setUpdatedAt(new \DateTime());
        $manager->persist($user);
        $manager->flush();
    }
}
