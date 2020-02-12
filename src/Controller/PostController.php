<?php
namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\SearchForm;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function index(Request $request): Response
    {
        $data = new SearchData();
        $form = $this->createForm(SearchForm::class, $data);
        $form->handleRequest($request);
        [$min, $max] = $this->repository->findMinMax($data);
        $tag = '';
        if ($request->query->get('tag')) {
            $tag = $request->query->get('tag');
            $posts = $this->repository->findLatest($request->query->getInt('page', 1), null, [$tag], 13);
        } else {
            // $posts = $this->repository->findLatest($request->query->getInt('page', 1), null, [], 13);
            $posts = $this->repository->filterPosts($data, $request->query->getInt('page', 1));
        }
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('post/_posts.html.twig', ['posts' => $posts]),
                'sorting' => $this->renderView('post/_sorting.html.twig', ['posts' => $posts]),
                'pagination' => $this->renderView('post/_pagination.html.twig', ['posts' => $posts]),
                'pages' => ceil($posts->getTotalItemCount() / $posts->getItemNumberPerPage()),
                'min' => $min,
                'max' => $max
            ]);
        }
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'tag' => $tag,
            'form' => $form->createView(),
            'min' => $min,
            'max' => $max
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
        PostRepository $postRepository, 
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
            $cache->invalidateTags(['posts']);

            return $this->redirectToRoute('post.show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ]);
        }
        $post->getViews();
        $post->setViews($post->getViews()+1);
        $this->em->persist($post);
        $this->em->flush();
        
        $tags = $post->getTags();
        $sets = [];
        foreach ($tags as $tag) {
            $sets[] = $tag->getName();
        }
        if ( $autorId = $request->query->get('author')) {
            $authorPosts = $postRepository->findPostsByField($request->query->getInt('page', 1), 'author', $autorId, $post->getId());
            $associatedPosts = $authorPosts;
        } else {
            $relatedPosts = $postRepository->findLatest($request->query->getInt('page', 1), null, $sets, 3, $post->getId());
            $associatedPosts = $relatedPosts;
        }
        if ($request->get('ajax')) {
            return new JsonResponse([
                'content' => $this->renderView('post/_associatedPosts.html.twig', ['associatedPosts' => $associatedPosts]),
                'pagination' => $this->renderView('post/_postPagination.html.twig', ['associatedPosts' => $associatedPosts]),
                'pages' => ceil($associatedPosts->getTotalItemCount() / $associatedPosts->getItemNumberPerPage())
            ]);
        }
        // dump($associatedPosts);
        $cache->invalidateTags(['popularPosts']);
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'associatedPosts' => $associatedPosts,
            'form' => $form->createView()
        ]);
    }
}