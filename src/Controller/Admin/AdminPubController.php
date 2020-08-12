<?php
namespace App\Controller\Admin;

use App\Entity\Pub;
use App\Form\PubType;
use App\Repository\PubRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminPubController extends AbstractController
{
    private $repository;

    private $em;
    
    public function __construct(PubRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/admin/pubs", name="admin.pub.index")
     */
    public function index(Request $request)
    {
        $page = $request->query->get('page', 1);
        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }
        $pubs = $this->repository->adminFindAll($page);
        return $this->render('admin/pub/index.html.twig', compact('pubs'));
    }

    /**
     * new
     * @Route("/admin/pub/create", name="admin.pub.new")
     * @return void
     */
    public function new(Request $request, TagAwareAdapterInterface $cache)
    {
        $pub = new Pub();
        $form = $this->createForm(PubType::class, $pub);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($pub);
            $this->em->flush();
            
            if ($pub->getSmallfile()) {
                $targetPath = 'media/pubs/' .  $pub->getSmallfile();
                $this->resizeImage($targetPath, 350, 230);
            }
            if ($pub->getLargefile()) {
                $targetPath = 'media/pubs/' .  $pub->getLargefile();
                $this->resizeImage($targetPath, 830, 100);
            }
            $this->addFlash('success', 'Elément bien créé avec succès');

            $cache->invalidateTags(['topPub', 'pub']);

            return $this->redirectToRoute('admin.pub.index');
        }
        return $this->render('admin/pub/new.html.twig', [
            'pub' => $pub,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/pub/{id}", name="admin.pub.edit", methods="GET|POST")
     */
    public function edit(Pub $pub, Request $request, TagAwareAdapterInterface $cache)
    {
        $form = $this->createForm(PubType::class, $pub);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            
            if ($pub->getSmallfile()) {
                $targetPath = 'media/pubs/' .  $pub->getSmallfile();
                $this->resizeImage($targetPath, 350, 230);
            }
            if ($pub->getLargefile()) {
                $targetPath = 'media/pubs/' .  $pub->getLargefile();
                $this->resizeImage($targetPath, 830, 100);
            }

            $this->addFlash('success', 'La publicité a été mise à jour avec succès');
            $cache->invalidateTags(['topPub', 'pub']);

            return $this->redirectToRoute('admin.pub.index');
        }
        return $this->render('admin/pub/edit.html.twig', [
            'pub' => $pub,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/pub/{id}", name="admin.pub.delete", methods="DELETE")
     */
    public function delete(Pub $pub, Request $request)
    {
        if ($this->isCsrfTokenValid('delete' . $pub->getId(), $request->get('_token'))) {
            $this->em->remove($pub);
            $this->em->flush();
            $this->addFlash('success', 'Publicité suprimée avec succès');
        }
        return $this->redirectToRoute('admin.pub.index');
    }

    private function resizeImage($targetPath, $width, $height)
    {
        $manager = new ImageManager(['driver' => 'gd']);
        $manager->make($targetPath)->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->save($targetPath);
    }
}