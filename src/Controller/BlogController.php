<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BlogController
 * @package App\Controller
 *
 * @Route("/blog")
 */
class BlogController extends AbstractController
{
    private const POSTS = [
        [
            'id' => 1,
            'slug' => 'hello-world',
            'title' => 'Hello World',
        ],
        [
            'id' => 2,
            'slug' => 'another-post',
            'title' => 'This is another post',
        ],
        [
            'id' => 3,
            'slug' => 'last-example',
            'title' => 'This is the last example',
        ],
    ];

    /**
     * @Route("/", name="blog_list", defaults={"page": 5})
     */
    public function list($page = 1): Response
    {
        return $this->json(
            [
                'page' => $page,
                'data' => self::POSTS
            ]
        );
    }

    /**
     * @Route("/{id}", name="blog_by_id", requirements={"id"="\d+"})
     */
    public function post($id): Response
    {
        return $this->json(
            self::POSTS[array_search($id, array_column(self::POSTS, 'id'))]
        );
    }

    /**
     * @Route("/{slug}", name="blog_by_slug")
     */
    public function postBySlug($slug): Response
    {
        return $this->json(
            self::POSTS[array_search($slug, array_column(self::POSTS, 'slug'))]
        );
    }
}
