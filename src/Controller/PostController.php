<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PostController extends AbstractController
{
    private $repository;

    private $em;

    public function __construct(PostRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }
    /**
     * @Route("/posts", name="post.index")
     * @return Response
     */
    public function index(Request $request, CategoryRepository $categories): Response
    {

        return $this->render('post/index.html.twig', [
            'current_menu' => 'posts',
            'posts' => $this->repository->paginateAllVisible($request->query->getInt('page', 1)),
            'categories' => $categories->findAll()
        ]);
    }

    /**
     * show
     * @Route("/posts/{slug}-{id}", name="post.show", requirements={"slug": "[a-z0-9\-]*"})
     * @param  mixed $post
     *
     * @return Response
     */
    public function show(
        Post $post, 
        string $slug, 
        Request $request,
        CommentController $commentController,
        TagAwareCacheInterface $cache
    ): Response
    {
        if ($post->getSlug() !== $slug) {
            return $this->redirectToRoute('post.show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ], 301);
        }
        $comment = new Comment();
        $comment->setPost($post);
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentController->add($comment);
            $this->addFlash('success', 'Votre commentaire a bien été posté');
            return $this->redirectToRoute('post.show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ]);
        }
        $post->getViews();
        $post->setViews($post->getViews()+1);
        $this->em->persist($post);
        $this->em->flush();
        $cache->invalidateTags(['popularPosts']);
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'current_menu' => 'posts',
            'form' => $form->createView()
        ]);
    }
}