<?php

namespace App\DataFixtures;

use App\Entity\{BlogPost, Comment, User};
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Faker\{Factory, Generator};
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;
    private Generator $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = Factory::create();
    }

    /**
     * @param ObjectManager $manager
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    /**
     * @param ObjectManager $manager
     * @throws Exception
     */
    private function loadBlogPosts(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference('user_admin');

        for ($i = 0; $i < 100; $i++) {
            $blogPost = (new BlogPost)
                ->setTitle($this->faker->realText(30))
                ->setPublished($this->faker->dateTimeThisYear)
                ->setAuthor($user)
                ->setContent($this->faker->realText())
                ->setSlug($this->faker->slug);

            $this->setReference("blog_post_$i", $blogPost);

            $manager->persist($blogPost);
        }

        $manager->flush();
    }

    private function loadComments(ObjectManager $manager)
    {
        /** @var User $user */
        $user = $this->getReference('user_admin');

        for ($i = 0; $i < 100; $i++) {
            /** @var BlogPost $post */
            $post = $this->getReference("blog_post_$i");

            for ($j = 0; $j < rand(1, 10); $j++) {
                $comment = (new Comment)
                    ->setAuthor($user)
                    ->setPublished($this->faker->dateTimeThisYear)
                    ->setContent($this->faker->realText())
                    ->setPost($post);

                $manager->persist($comment);
            }
        }

        $manager->flush();
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
