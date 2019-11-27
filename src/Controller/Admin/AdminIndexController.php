<?php
namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AdminIndexController extends AbstractController
{
    /**
     * @Route("/admin", name="admin.index")
     */
    public function index(Request $request)
    {
        return $this->render('admin/index.html.twig');
    }
}