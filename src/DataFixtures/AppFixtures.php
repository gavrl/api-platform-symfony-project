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

    /**
     * @var Generator
     */
    private Generator $faker;

    private const USERS = [
        [
            'username' => 'admin',
            'email' => 'admin@blog.com',
            'name' => 'Admin',
            'password' => 'admin',
            'roles' => [User::ROLE_SUPERADMIN],
        ],
        [
            'username' => 'gavrl',
            'email' => 'gavrl@blog.com',
            'name' => 'Sergey Gavr',
            'password' => 'secret123#',
            'roles' => [User::ROLE_ADMIN],
        ],
        [
            'username' => 'rob_smith',
            'email' => 'rob@blog.com',
            'name' => 'Rob Smith',
            'password' => 'secret123#',
            'roles' => [User::ROLE_WRITER],
        ],
        [
            'username' => 'jenny_rowling',
            'email' => 'jenny@blog.com',
            'name' => 'Jenny Rowling',
            'password' => 'secret123#',
            'roles' => [User::ROLE_WRITER],
        ],
        [
            'username' => 'han_solo',
            'email' => 'han@blog.com',
            'name' => 'Han Solo',
            'password' => 'secret123#',
            'roles' => [User::ROLE_EDITOR],
        ],
        [
            'username' => 'jedi_knight',
            'email' => 'jedi@blog.com',
            'name' => 'Jedi Knight',
            'password' => 'secret123#',
            'roles' => [User::ROLE_COMMENTATOR],
        ],
    ];

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
        for ($i = 0; $i < 100; $i++) {
            $blogPost = (new BlogPost)
                ->setTitle($this->faker->realText(30))
                ->setPublished($this->faker->dateTimeThisYear)
                ->setContent($this->faker->realText())
                ->setSlug($this->faker->slug);

            $authorReference = $this->getRandomUserReference($blogPost);

            $blogPost->setAuthor($authorReference);

            $this->setReference("blog_post_$i", $blogPost);

            $manager->persist($blogPost);
        }

        $manager->flush();
    }

    private function loadComments(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            /** @var BlogPost $post */
            $post = $this->getReference("blog_post_$i");

            for ($j = 0; $j < rand(1, 10); $j++) {
                $comment = (new Comment)
                    ->setPublished($this->faker->dateTimeThisYear)
                    ->setContent($this->faker->realText())
                    ->setPost($post);

                $authorReference = $this->getRandomUserReference($comment);

                $comment->setAuthor($authorReference);

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
        foreach (self::USERS as $userFixture) {
            $user = (new User())
                ->setUsername($userFixture['username'])
                ->setEmail($userFixture['email'])
                ->setName($userFixture['name'])
                ->setRoles($userFixture['roles']);

            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $userFixture['password']
                )
            );

            $this->addReference('user_' . $userFixture['username'], $user);

            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @param $entity
     * @return User
     */
    protected function getRandomUserReference($entity): User
    {
        $randomUser = self::USERS[rand(0, 5)];

        if ($entity instanceof BlogPost && !count(
                array_intersect(
                    $randomUser['roles'],
                    [User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_WRITER]
                )
            )) {
            return $this->getRandomUserReference($entity);
        }

        if ($entity instanceof Comment && !count(
                array_intersect(
                    $randomUser['roles'],
                    [
                        User::ROLE_SUPERADMIN,
                        User::ROLE_ADMIN,
                        User::ROLE_WRITER,
                        User::ROLE_COMMENTATOR,
                    ]
                )
            )) {
            return $this->getRandomUserReference($entity);
        }

        /** @var User $user */
        $user = $this->getReference(
            'user_' . $randomUser['username']
        );

        return $user;
    }
}
