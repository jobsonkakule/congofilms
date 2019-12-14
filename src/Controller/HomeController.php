<?php
namespace App\Controller;

use App\Repository\PostRepository;
use Google_Client;
use Google_Service_YouTube;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
    
    /**
     * index
     * @Route("/", name="home")
     * @param  mixed $repository
     *
     * @return Response
     */
    public function index(PostRepository $repository, Request $request, TagAwareAdapterInterface $cache): Response
    {
        // Cacche invalidation
        // $cache->invalidateTags(['lastPosts']);
        $posts = $repository->findLatest($request->query->getInt('page', 1));
        $topPosts = $repository->findTopPosts();
        // $categories = $this->getParameter('cat').findAll();
        // dump($categories);
        $popularPosts = $repository->findPopularPosts();
        $filteredPosts = $this->filterPosts($topPosts);
        // $search = new PostSearch();
        // $form = $this->createForm(PostSearchType::class, $search);  
        // $form->handleRequest($request);
        if ($request->query->get('query')) {
            $query = $request->query->get('query');
            if (!empty($query) && strlen($query) > 3) {
                $queryPosts = $repository->findLatest($request->query->getInt('page', 1), $query, null, 7);
            } else {
                $queryPosts = [];
                // dump($query);die();
            }
            return $this->render('post/index.search.html.twig', [
                'posts' => $queryPosts,
                'q' => $query,
            ]);
        }
        return $this->render('views/home.html.twig', [
            'posts' => $posts,
            'popularPosts' => $popularPosts,
            'topPosts' => $filteredPosts,
        ]);
    }

    /**
     * showVideos
     * @Route("/videos", name="video.index")
     * @return void
     */
    public function showVideos()
    {
        $key = "AIzaSyCPmYWVrORHMnJXXs24V7BkFHHcx9t3T3Q";
        $client = new Google_Client();
        
        $client->setDeveloperKey($key);
        //When working in dev-environment
        $guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
        $client->setHttpClient($guzzleClient);

        $youtube = new Google_Service_YouTube($client);

        // $channel = $youtube->channels->listChannels('contentDetails', ['id' => 'UC4_mlXKezTbWDxrLihjvxNw']);
        $playlist = $youtube->playlistItems->listPlaylistItems('id,snippet,contentDetails', ['playlistId' => 'UU4_mlXKezTbWDxrLihjvxNw', 'maxResults' => 12]);
        // $response = $youtube->search->listSearch('id,snippet', ['q' => 'mjcn', 'order' => 'relevance', 'maxResults' => 12, 'type' => 'video']);
        // $first = $youtube->videos->listVideos('id,snippet,contentDetails', ['id' => $response['items'][0]['id']['videoId']])['items'][0];

        return $this->render('video/index.html.twig', compact('playlist'));
    }

    private function filterPosts($topPosts)
    {
        $result = [];
        foreach ($topPosts as $element) {
            $result[$element['id']][] = $element;
        }
        $shifted = [];
        foreach ($result as $element) {
            $shifted[] = array_shift($element);
        }
        return $shifted;
    }
}