<?php
namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    private $repository;

    private $em;

    public function __construct(CategoryRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }
    /**
     * @Route("/categories", name="category.index")
     * @return Response
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {

        return $this->render('category/index.html.twig', [
            'current_menu' => 'categories',
            'categories' => $this->repository->findAll()
        ]);
    }

    /**
     * show
     * @Route("/categories/{slug}-{id}", name="category.show", requirements={"slug": "[a-z0-9\-]*"})
     * @param  mixed $post
     *
     * @return Response
     */
    public function show(
        Category $category, 
        string $slug,
        Request $request,
        PostRepository $posts
    ): Response
    {
        if ($category->getSlug() !== $slug) {
            return $this->redirectToRoute('category.show', [
                'id' => $category->getId(),
                'slug' => $category->getSlug()
            ], 301);
        }

        return $this->render('category/show.html.twig', [
            'category' => $category,
            'posts' => $posts->findWithCategory($category->getId(), $request->query->getInt('page', 1)),
            'current_menu' => 'categories'
        ]);
    }
}