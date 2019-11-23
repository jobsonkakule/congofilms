<?php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
    
    /**
     * index
     * @Route("/", name="home")
     * @param  mixed $repository
     *
     * @return Response
     */
    public function index(PostRepository $repository, CategoryRepository $categories): Response
    {
        $posts = $repository->findLatest();
        $categories = $categories->findAll();
        return $this->render('views/home.html.twig', [
            'posts' => $posts,
            'categories' => $categories
        ]);
    }
}