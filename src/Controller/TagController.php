<?php
namespace App\Controller;

use App\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
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
}