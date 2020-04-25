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
        $urls[] = ['loc' =>  $this->generateUrl('post.index')];
        $urls[] = ['loc' =>  $this->generateUrl('video.index')];
        $urls[] = ['loc' =>  $this->generateUrl('home.contact')];
        $urls[] = ['loc' =>  $this->generateUrl('login')];
        $urls[] = ['loc' =>  $this->generateUrl('signup')];

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
        /**
         * @var Post[] $posts
         */
        $posts = $this->getDoctrine()->getRepository(Post::class)->findAll();
        foreach(array_reverse($posts) as $post) {
            $picture = $post->getPictures();
            if (!is_null($picture[0])) {
                $images = [
                    'loc' => '/media/posts/' . $picture[0]->getFileName(),
                    'title' => $post->getTitle()
                ];
            } else {
                $images = [];
            }
            
            $urls[] = [
                'loc' => $this->generateUrl('post.show', [
                    'id' => $post->getId(),
                    'slug' => $post->getSlug()
                ]),
                'image' => $images,
                'lastmod' => $post->getUpdatedAt()->format('Y-m-d')
            ];
        }
        
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
