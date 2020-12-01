<?php
namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminSecurityController extends AbstractController
{
    private $repository;

    private $em;

    public function __construct(UserRepository $repository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    /**
     * @Route("/admin/users", name="admin.user.index")
     */
    public function index(Request $request)
    {
        $page = $request->query->get('page', 1);
        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }
        $users = $this->repository->adminFindAll($page);
        return $this->render('admin/user/index.html.twig', compact('users'));
    }

    /**
     * @Route("/admin/user/{id}", name="admin.user.edit", methods="GET|POST")
     */
    public function edit(User $user, Request $request, TagAwareAdapterInterface $cache)
    {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            
            $this->addFlash('success', 'L\'utilisateur a été mis à jour avec succès');
            $cache->invalidateTags(['users']);

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
    public function delete(User $user, Request $request, TagAwareAdapterInterface $cache)
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success', 'Utilisateur suprimé avec succès');
            $cache->invalidateTags(['users']);
        }
        return $this->redirectToRoute('admin.user.index');
    }
}