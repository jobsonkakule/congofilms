<?php
namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
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
        UserRepository $userRepository
    )
    {
        $nbPosts = count($postRepository->findAll());
        $nbCategories = count($categoryRepository->findAll());
        $nbTags = count($tagRepository->adminFindAll());
        $nbUsers = count($userRepository->findAll());
        return $this->render('admin/index.html.twig', compact('nbPosts', 'nbCategories', 'nbTags', 'nbUsers'));
    }
}