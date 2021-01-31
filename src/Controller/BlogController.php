<?php

namespace App\Controller;

use App\Entity\BlogPost;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
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
     * @Route("/{page}", name="blog_list", defaults={"page": 5}, requirements={"page"="\d+"}, methods={"GET"})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function list(Request $request, $page = 1): Response
    {
        $limit = $request->get('limit', 10);

        return $this->json(
            [
                'page' => $page,
                'limit' => $limit,
                'data' => array_map(
                    function ($item) {
                        return $this->generateUrl('blog_by_id', ['id' => $item['id']]);
                    },
                    self::POSTS
                )
            ]
        );
    }

    /**
     * @Route("/post/{id}", name="blog_by_id", requirements={"id"="\d+"})
     *
     * @param $id
     * @return Response
     */
    public function post($id): Response
    {
        return $this->json(
            self::POSTS[array_search($id, array_column(self::POSTS, 'id'))]
        );
    }

    /**
     * @Route("/post/{slug}", name="blog_by_slug")
     *
     * @param $slug
     * @return Response
     */
    public function postBySlug($slug): Response
    {
        return $this->json(
            self::POSTS[array_search($slug, array_column(self::POSTS, 'slug'))]
        );
    }

    /**
     * @Route("/add", name="blog_add", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Request $request): Response
    {
        $serializer = $this->get('serializer');
        $blogPost = $serializer->deserialize($request->getContent(), BlogPost::class, 'json');

        $em = $this->getDoctrine()->getManager();
        $em->persist($blogPost);
        $em->flush();

        return $this->json($blogPost);
    }
}
