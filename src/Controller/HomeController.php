<?php
namespace App\Controller;

use App\Entity\PostSearch;
use App\Form\PostSearchType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(PostRepository $repository, CategoryRepository $categories, Request $request, TagAwareAdapterInterface $cache): Response
    {
        // Cacche invalidation
        // $cache->invalidateTags(['lastPosts']);
        $posts = $repository->findLatest();
        $categories = $categories->findAll();
        $topPosts = $repository->findTopPosts();
        $popularPosts = $repository->findPopularPosts();
        $filteredPosts = $this->filterPosts($topPosts);
        $search = new PostSearch();
        $form = $this->createForm(PostSearchType::class, $search);  
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $query = $request->query->get('query');
            return $this->render('post/index.search.html.twig', [
                'posts' => $repository->findPost($request->query->getInt('page', 1), $query),
                'categories' => $categories,
                'form' => $form->createView(),
                'q' => $query
            ]);
        }
        return $this->render('views/home.html.twig', [
            'posts' => $posts,
            'popularPosts' => $popularPosts,
            'topPosts' => $filteredPosts,
            'categories' => $categories,
            'form' => $form->createView()
        ]);
    }

    private function filterPosts($topPosts)
    {
        $result = [];
        foreach ($topPosts as $element) {
            $result[$element['id']][] = $element;
        }
        $shifted = [];
        foreach ($result as $element) {
            $shifted[] = array_shift($element);
        }
        return $shifted;
    }
}