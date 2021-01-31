<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);

    }

    private function loadBlogPosts(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference('user_admin');
        $blogPost = (new BlogPost)
            ->setTitle('A first post')
            ->setPublished(new DateTime())
            ->setAuthor($user)
            ->setContent('Post text')
            ->setSlug('a-first-post');

        $manager->persist($blogPost);

        $blogPost = (new BlogPost)
            ->setTitle('A second post')
            ->setPublished(new DateTime())
            ->setAuthor($user)
            ->setContent('Another post text')
            ->setSlug('a-second-post');

        $manager->persist($blogPost);

        $manager->flush();
    }

    private function loadComments()
    {

    }

    /**
     * @param ObjectManager $manager
     */
    private function loadUsers(ObjectManager $manager)
    {
        $user = (new User)
            ->setUsername('Gavrl')
            ->setEmail('sergeygavr.94@yandex.com')
            ->setName('Sergey');

        $user->setPassword($this->passwordEncoder->encodePassword($user,'12345'));

        $this->addReference('user_admin', $user);

        $manager->persist($user);
        $manager->flush();
    }
}
