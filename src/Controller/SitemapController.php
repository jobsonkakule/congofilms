<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    /**
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format" = "xml"})
     */
    public function index(Request $request)
    {
        $hostname = $request->getSchemeAndHttpHost();
        $urls = [];

        // adding static URLs
        $urls[] = ['loc' =>  $this->generateUrl('home')];

        // adding dynamic URLs
        /**
         * @var User[] $users
         */
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        foreach($users as $user) {
            
            if (in_array('ROLE_EDITOR', $user->getRoles())) {
                if ($user->getFilename()) {
                    $images = [
                        'loc' => '/media/users/' . $user->getFileName(),
                        'title' => $user->getPseudo() ?? $user->getUsername()
                    ];
                } else {
                    $images = [];
                }
                
                $urls[] = [
                    'loc' => $this->generateUrl('profil.show', [
                        'id' => $user->getId()
                    ]),
                    'image' => $images,
                    'lastmod' => $user->getUpdatedAt()->format('Y-m-d')
                ];
            }
        }

        // adding dynamic URLs
        
        // Create Response
        $response =  new Response(
            $this->renderView('sitemap/index.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ]),
            200
        );
        // Add HTTP headers
        $response->headers->set('Content-Type', 'text/xml');
        
        return $response;
    }
}
