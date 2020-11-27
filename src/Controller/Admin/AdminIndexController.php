<?php
namespace App\Controller\Admin;

use App\Repository\CategoryRepository;
use App\Repository\PhotoRepository;
use App\Repository\PubRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AdminIndexController extends AbstractController
{
    /**
     * @Route("/admin", name="admin.index")
     */
    public function index(
        Request $request,
        PhotoRepository $photoRepository,
        VideoRepository $videoRepository,
        UserRepository $userRepository,
        PubRepository $pubRepository
    )
    {
        $nbPhotos = $photoRepository->countAll();
        $nbVideos = $videoRepository->countAll();
        $nbUsers = $userRepository->countAll();
        $nbPubs = $pubRepository->countAll();
        return $this->render('admin/index.html.twig', compact('nbPhotos', 'nbVideos', 'nbUsers', 'nbPubs'));
    }
}