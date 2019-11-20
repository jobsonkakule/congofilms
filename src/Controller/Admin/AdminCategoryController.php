<?php
namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\CategoryType;
use App\Form\PostType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AdminCategoryController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $repository;

    private $em;

    public function __construct(CategoryRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * index
     * @Route("/admin/categories", name="admin.category.index")
     * @return void
     */
    public function index(Request $request)
    {
        $categories = $this->repository->findAll();
        return $this->render('admin/category/index.html.twig', compact('categories'));
    }

    /**
     * new
     * @Route("/admin/category/create", name="admin.category.new")
     * @return void
     */
    public function new(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($category);
            $this->em->flush();
            $this->addFlash('success', 'Elément créé avec succès');

            return $this->redirectToRoute('admin.category.index');
        }
        
        return $this->render('admin/category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * edit
     * @Route("/admin/category/{id}", name="admin.category.edit", methods="GET|POST")
     * @param  Category $category
     *
     * @return void
     */
    public function edit(Category $category,Request $request)
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Elément modifié avec succès');
            return $this->redirectToRoute('admin.category.index');
        }
        return $this->render('admin/category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * delete
     * @Route("/admin/category/{id}", name="admin.category.delete", methods="DELETE")
     * @param  mixed $category
     *
     * @return void
     */
    public function delete(Category $category, Request $request)
    {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->get('_token'))) {
            $this->em->remove($category);
            $this->em->flush();
            $this->addFlash('success', 'Elément suprimmé avec succès');
        }
        return $this->redirectToRoute('admin.category.index');

    }
}