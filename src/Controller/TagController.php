<?php
namespace App\Controller;

use App\Entity\Tag;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    private $repository;

    private $em;

    public function __construct(PostRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }
    /**
     * @Route("/tags.json", name="tag.index")
     * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $tagRepository = $this->getDoctrine()->getRepository(Tag::class);
        $tags = $tagRepository->findAll();
        return $this->json($tags);
    }

    /**
     * index
     * @Route("/admin/tags", name="admin.tag.index")
     * @return void
     */
    public function adminIndex(Request $request)
    {
        $tagRepository = $this->getDoctrine()->getRepository(Tag::class);
        $tags = $tagRepository->adminFindAll();
        return $this->render('admin/tag/index.html.twig', compact('tags'));
    }

    /**
     * delete
     * @Route("/admin/tag/{id}", name="admin.tag.delete", methods="DELETE")
     * @param  mixed $tag
     *
     * @return void
     */
    public function delete(Tag $tag, Request $request)
    {
        if ($this->isCsrfTokenValid('delete' . $tag->getId(), $request->get('_token'))) {
            $this->em->remove($tag);
            $this->em->flush();
            $this->addFlash('success', 'Bien suprimé avec succès');
        }
        return $this->redirectToRoute('admin.tag.index');

    }
}