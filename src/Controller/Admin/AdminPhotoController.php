<?php
namespace App\Controller\Admin;

use App\Entity\Photo;
use App\Form\EditPhotoType;
use App\Form\PhotoType;
use App\Repository\PhotoRepository;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class AdminPhotoController extends AbstractController
{
    private $repository;

    private $em;

    public function __construct(PhotoRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/admin/photos", name="admin.photo.index")
     */
    public function index(Request $request)
    {
        $page = $request->query->get('page', 1);
        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }
        $photos = $this->repository->adminFindAll($page);
        return $this->render('admin/photo/index.html.twig', compact('photos'));
    }

    /**
     * @Route("/admin/photo/create", name="admin.photo.new")
     */
    public function new(Request $request, TagAwareCacheInterface $cache)
    {
        $photo = new Photo();
        $form = $this->createForm(PhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($photo);
            $this->em->flush();

            if ($photo->getFilename())
            {
                $targetPath = 'media/photos/' .  $photo->getFilename();
                $this->resizeImage($targetPath);
            }

            $this->addFlash('success', 'La Photo a été ahoutée avec succès');
            $cache->invalidateTags(['photos']);
            return $this->redirectToRoute('admin.photo.index');
        }
        return $this->render('admin/photo/new.html.twig', [
            'photo' => $photo,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/photo/{id}", name="admin.photo.edit", methods="GET|POST")
     */
    public function edit(Photo $photo, Request $request, TagAwareAdapterInterface $cache)
    {
        dump($photo);
        $form = $this->createForm(EditPhotoType::class, $photo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            if ($photo->getFilename())
            {
                $targetPath = 'media/photos/' .  $photo->getFilename();
                $this->resizeImage($targetPath);
            }

            $this->addFlash('success', 'La Photo a été mise à jour avec succès');
            $cache->invalidateTags(['photos']);

            return $this->redirectToRoute('admin.photo.index');
        }
        return $this->render('admin/photo/edit.html.twig', [
            'photo' => $photo,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/photo/{id}", name="admin.photo.delete", methods="DELETE")
     */
    public function delete(Photo $photo, Request $request, TagAwareAdapterInterface $cache)
    {
        if ($this->isCsrfTokenValid('delete' . $photo->getId(), $request->get('_token'))) {
            $this->em->remove($photo);
            $this->em->flush();
            $this->addFlash('success', 'Photo suprimée avec succès');
            $cache->invalidateTags(['photos']);
        }
        return $this->redirectToRoute('admin.photo.index');
    }

    private function resizeImage($targetPath)
    {
        $manager = new ImageManager(['driver' => 'gd']);
        $manager->make($targetPath)->widen(1024, function ($constraint) {
            $constraint->upsize();
        })->save($targetPath);
    }
}