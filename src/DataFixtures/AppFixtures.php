<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $blogPost = (new BlogPost)
            ->setTitle('A first post')
            ->setPublished(new DateTime())
            ->setAuthor('Sergey Gavr')
            ->setContent('Post text')
            ->setSlug('a-first-post');

        $manager->persist($blogPost);

        $blogPost = (new BlogPost)
            ->setTitle('A second post')
            ->setPublished(new DateTime())
            ->setAuthor('Sergey Gavr')
            ->setContent('Another post text')
            ->setSlug('a-second-post');

        $manager->persist($blogPost);

        $manager->flush();
    }
}
