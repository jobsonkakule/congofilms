<?php
namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\PubRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AdminIndexController extends AbstractController
{
    /**
     * @Route("/admin", name="admin.index")
     */
    public function index(
        Request $request,
        PostRepository $postRepository,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        UserRepository $userRepository,
        PubRepository $pubRepository
    )
    {
        $nbPosts = $postRepository->countAll();
        $nbCategories = $categoryRepository->countAll();
        $nbTags = count($tagRepository->adminFindAll());
        $nbUsers = $userRepository->countAll();
        $nbPubs = $pubRepository->countAll();
        return $this->render('admin/index.html.twig', compact('nbPosts', 'nbCategories', 'nbTags', 'nbUsers', 'nbPubs'));
    }
}