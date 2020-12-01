<?php
namespace App\Controller\Admin;

use App\Entity\Video;
use App\Form\VideoType;
use App\Repository\VideoRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AdminVideoController extends AbstractController
{
    /**
     * @var VideoRepository
     */
    private $repository;

    private $em;

    public function __construct(VideoRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * index
     * @Route("/admin/videos", name="admin.video.index")
     * @return void
     */
    public function index(Request $request)
    {
        $videos = $this->repository->findAdmin($request->query->getInt('page', 1));
        return $this->render('admin/video/index.html.twig', compact('videos'));
    }

    /**
     * new
     * @Route("/admin/video/create", name="admin.video.new")
     * @return void
     */
    public function new(Request $request, TagAwareAdapterInterface $cache)
    {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($video);
            $this->em->flush();

            $this->addFlash('success', 'Elément créé avec succès');
            $cache->invalidateTags(['videos']);

            return $this->redirectToRoute('admin.video.index');
        }
        
        return $this->render('admin/video/new.html.twig', [
            'video' => $video,
            'form' => $form->createView()
        ]);
    }

    /**
     * edit
     * @Route("/admin/video/{id}", name="admin.video.edit", methods="GET|POST")
     * @param  Video $video
     *
     * @return void
     */
    public function edit(Video $video,Request $request, TagAwareAdapterInterface $cache)
    {
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Elément modifié avec succès');
            $cache->invalidateTags(['videos']);

            return $this->redirectToRoute('admin.video.index');
        }
        return $this->render('admin/video/edit.html.twig', [
            'video' => $video,
            'form' => $form->createView()
        ]);
    }

    /**
     * delete
     * @Route("/admin/video/{id}", name="admin.video.delete", methods="DELETE")
     * @param  mixed $video
     *
     * @return void
     */
    public function delete(VIdeo $video, Request $request, TagAwareAdapterInterface $cache)
    {
        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->get('_token'))) {
            $this->em->remove($video);
            $this->em->flush();
            $this->addFlash('success', 'Elément suprimmé avec succès');
            $cache->invalidateTags(['videos']);
        }
        return $this->redirectToRoute('admin.video.index');

    }
}