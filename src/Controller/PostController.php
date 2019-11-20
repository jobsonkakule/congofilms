<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Post;
use App\Entity\PostSearch;
use App\Form\ContactType;
use App\Form\PostSearchType;
use App\Notification\ContactNotification;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        CategoryRepository $categories,
        ContactNotification $notification,
        PostRepository $repository
    ): Response
    {
        if ($post->getSlug() !== $slug) {
            return $this->redirectToRoute('post.show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ], 301);
        }
        $contact = new Contact();
        $contact->setProperty($post);
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notification->notify($contact);
            $this->addFlash('success', 'Votre email a bien été envoyé');
            return $this->redirectToRoute('post.show', [
                'id' => $post->getId(),
                'slug' => $post->getSlug()
            ]);
        }
        $post->getViews();
        $post->setViews($post->getViews()+1);
        $this->em->persist($post);
        $this->em->flush();
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'posts' => $repository->findLatest(),
            'categories' => $categories->findAll(),
            'current_menu' => 'posts',
            'form' => $form->createView()
        ]);
    }
}