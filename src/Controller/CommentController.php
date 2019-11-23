<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class CommentController extends AbstractController
{

    public function add(Comment $comment)
    {
        $this->getDoctrine()->getManager()->persist($comment);
        $this->getDoctrine()->getManager()->flush();
    }

    /**
     * @Route("/comment/{id}", name="comment.delete", methods={"DELETE"})
     */
    public function delete(Comment $comment, Request $request, CommentRepository $commentRepository)
    {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $data['_token'])) {
            $entityManager = $this->getDoctrine()->getManager();
            $commentRepository->deleteComments($comment->getId());
            $entityManager->remove($comment);
            $entityManager->flush();
            return new JsonResponse(['success' => 1]);
        }
        return new JsonResponse(['error' => 'Token invalide'], 400);

    }
}