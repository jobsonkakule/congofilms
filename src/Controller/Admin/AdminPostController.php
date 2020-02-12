<?php
namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AdminPostController extends AbstractController
{
    /**
     * @var PostRepository
     */
    private $repository;

    private $em;

    public function __construct(PostRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * index
     * @Route("/admin/posts", name="admin.post.index")
     * @return void
     */
    public function index(Request $request)
    {
        $repository = $this->repository;
        $postQuery = $request->query->get('postQuery');
        if ($tag = $request->query->get('tag')) {
            $posts = $repository->paginatePostForTag($request->query->getInt('page', 1), $tag);
        } elseif($postQuery) {
            $posts = $repository->paginatePostForTag($request->query->getInt('page', 1), null, $postQuery);
        }
        else {
            $posts = $repository->paginatePostForTag($request->query->getInt('page', 1));
            $postQuery;
        }
        return $this->render('admin/post/index.html.twig', compact('posts', 'postQuery'));
    }

    /**
     * new
     * @Route("/admin/post/create", name="admin.post.new")
     * @return void
     */
    public function new(Request $request, TagAwareAdapterInterface $cache)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $this->em->persist($post);
            $this->em->flush();
            
            $pics = [];
            foreach ($post->getPictures() as $picture) {
                $targetPath = 'media/posts/' .  $picture->getFileName();
                $pics[] = $targetPath;
                $this->resizeImage($targetPath);
            }
            $this->addFlash('success', 'Elément bien créé avec succès');
            $this->repository->adminRestScore();
            $cache->invalidateTags(['posts', 'lastPosts']);

            return $this->redirectToRoute('admin.post.index');
        }
        return $this->render('admin/post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    /**
     * edit
     * @Route("/admin/post/{id}", name="admin.post.edit", methods="GET|POST")
     * @param  Post $post
     *
     * @return void
     */
    public function edit(Post $post,Request $request, TagAwareAdapterInterface $cache)
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTime());
            $this->em->flush();
            $pics = [];
            foreach ($post->getPictures() as $picture) {
                $targetPath = 'media/posts/' .  $picture->getFileName();
                $pics[] = $targetPath;
                $this->resizeImage($targetPath);
            }
            $this->repository->adminRestScore();
            $cache->invalidateTags(['posts']);

            $this->addFlash('success', 'Elément bien modifié avec succès');
            return $this->redirectToRoute('admin.post.index');
        }
        return $this->render('admin/post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    /**
     * delete
     * @Route("/admin/post/{id}", name="admin.post.delete", methods="DELETE")
     * @param  mixed $post
     *
     * @return void
     */
    public function delete(Post $post, Request $request, TagAwareAdapterInterface $cache)
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->get('_token'))) {
            $this->em->remove($post);
            $this->em->flush();
            $this->addFlash('success', 'Bien suprimé avec succès');
        }
        $cache->invalidateTags(['posts']);

        return $this->redirectToRoute('admin.post.index');

    }

    private function resizeImage($targetPath)
    {
        $manager = new ImageManager(['driver' => 'gd']);
        $manager->make($targetPath)->widen(730, function ($constraint) {
            $constraint->upsize();
        })->save($targetPath);
    }
}