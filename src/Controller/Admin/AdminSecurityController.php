<?php
namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminSecurityController extends AbstractController
{
    private $repository;

    private $em;

    public function __construct(UserRepository $repository, ObjectManager $em)
    {
        $this->repository =$repository;
        $this->em = $em;
    }

    /**
     * @Route("/admin/users", name="admin.user.index")
     */
    public function index(Request $request)
    {
        $users = $this->repository->findAll();
        return $this->render('admin/user/index.html.twig', compact('users'));
    }

    /**
     * @Route("/admin/user/{id}", name="admin.user.edit", methods="GET|POST")
     */
    public function edit(User $user, Request $request)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dump($this->form);die();
            $this->em->flush();
            
            $this->addFlash('success', 'L\'utilisateur a été mis à jour avec succès');
            return $this->redirectToRoute('admin.user.index');
        }
        return $this->render('admin/user/edit.html.twig', [
            'post' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user/{id}", name="admin.user.delete", methods="DELETE")
     */
    public function delete(User $user, Request $request)
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success', 'Utilisateur suprimé avec succès');
        }
        return $this->redirectToRoute('admin.user.index');
    }
}