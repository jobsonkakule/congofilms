<?php
namespace App\Twig;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use App\Repository\PubRepository;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Google_Client;
use Google_Service_YouTube;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SidebarExtension extends AbstractExtension {

    private $postRepository;

    private $categoryRepository;

    private $pubRepository;

    private $twig;

    private $cache;

    private $socialCache;

    public function __construct(
        PostRepository $postRepository,
        CategoryRepository $categoryRepository,
        PubRepository $pubRepository,
        Environment $twig,
        TagAwareAdapterInterface $cache,
        CacheInterface $socialCache
    )
    {
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
        $this->pubRepository = $pubRepository;
        $this->twig = $twig;
        $this->cache = $cache;
        $this->socialCache = $socialCache;
    }
    public function getFunctions(): array
    { 
        return [
            new TwigFunction('sidebar', [$this, 'getSidebar'], ['is_safe' => ['html']]),
            new TwigFunction('footer', [$this, 'getFooter'], ['is_safe' => ['html']]),
            new TwigFunction('topPub', [$this, 'getTopPub'], ['is_safe' => ['html']]),
            new TwigFunction('pub', [$this, 'getPub'], ['is_safe' => ['html']]),
            new TwigFunction('social', [$this, 'getSocial'], ['is_safe' => ['html']]),
        ];
    }

    public function getSidebar(): string
    {
        return $this->cache->get('sidebar', function (ItemInterface $item) {
            $item->tag(['posts']);
            return $this->renderSidebar();
        });
    }

    public function getFooter(): string
    {
        return $this->cache->get('footer', function (ItemInterface $item) {
            $item->tag(['posts', 'categories', 'popularPosts']);
            return $this->renderFooter();
        });
    }

    public function getTopPub(): string
    {
        return $this->cache->get('topPub', function (ItemInterface $item) {
            $item->tag(['largePub', 'smallPub']);
            return $this->renderTopPub();
        });
    }

    public function getPub(): string
    {
        return $this->cache->get('pub', function (ItemInterface $item) {
            $item->tag(['largePub', 'smallPub']);
            return $this->renderPub();
        });
    }

    public function getSocial()
    {
        return $this->cache->get('social', function (ItemInterface $item) {
            $item->expiresAfter(86400);
            return $this->renderSocial();
        });
    }

    private function renderSidebar(): string {
        $posts = $this->postRepository->findForSidebar();
        return $this->twig->render('partials/sidebar.html.twig', [
            'posts' => $posts
        ]);
    }

    private function renderFooter(): string {
        $posts = $this->postRepository->findForSidebar();
        $popularPosts = $this->postRepository->findPopularPosts();
        $categories = $this->categoryRepository->findForFooter();
        return $this->twig->render('partials/footer.html.twig', [
            'posts' => $posts,
            'popularPosts' => $popularPosts,
            'categories' => $categories
        ]);
    }

    private function renderTopPub(): string {
        $pub = $this->pubRepository->findOneBy(['promo' => 1]);
        return $this->twig->render('partials/topPub.html.twig', [
            'topPub' => $pub
        ]);
    }

    private function renderPub(): string {
        $pub = $this->pubRepository->findOneBy(['promo' => 1]);
        return $this->twig->render('partials/pub.html.twig', [
            'pub' => $pub
        ]);
    }

    
    private function renderSocial(): string {
        $subscribers = number_format($this->getSubsribers(), 0, '', ' ');
        $followers = number_format($this->getFollowers(), 0, '', ' ');
        $likes = number_format($this->getLikes(), 0, '', ' ');
        return $this->twig->render('partials/social.html.twig', [
            'subscribers' => $subscribers,
            'followers' => $followers,
            'likes' => $likes
        ]);
    }

    private function getSubsribers()
    {
        $key = "AIzaSyCPmYWVrORHMnJXXs24V7BkFHHcx9t3T3Q";
        $client = new Google_Client();
        
        $client->setDeveloperKey($key);
        //When working in dev-environment
        $guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
        $client->setHttpClient($guzzleClient);

        $youtube = new Google_Service_YouTube($client);

        // $channel = $youtube->channels->listChannels('contentDetails', ['id' => 'UC4_mlXKezTbWDxrLihjvxNw']);
        $subscribers = $youtube->channels->listChannels('statistics', ['id' => 'UCB0erOivnkO7jkdHFQ_pa7Q']);

        return $subscribers->getItems()[0]->getStatistics()->getSubscriberCount();
    }

    private function getFollowers()
    {
        // $tw_username = 'GrandsLacsNews'; 
        // $data = file_get_contents('https://cdn.syndication.twimg.com/widgets/followbutton/info.json?screen_names='.$tw_username); 
        // $parsed =  json_decode($data,true);
        // $followers =  $parsed[0]['followers_count'];
        // if ($followers && is_numeric($followers)) {
        //     return $followers;
        // } else {
        //     return 0;
        // }
        return 10;
    }

    private function getLikes()
    {
        $fb = new Facebook([
            'app_id' => '454671828554424',
            'app_secret' => '826e36db816f2daa40a9fe653f6a5e68',
            'default_graph_version' => 'v2.10',
        ]);
        
        try {
            $response = $fb->get('/108304060702176?fields=fan_count&access_token=EAAGdhYjUkrgBAD0PoM93ThY7BRYhnvERnhIWV8yVtbl6LkyBkUnYt4NbfVtkF8TjHAJ0ST39O4XntFZCmEgfpmaTlG4peIbeviCSt7pOQK0gmoyiju7mERLlNK7h8ZBnSkCA4nbZCTvZBBcdyKOxZCXWd6A4xI8VP5Us19sUr0wZDZD', 'EAAGdhYjUkrgBAD0PoM93ThY7BRYhnvERnhIWV8yVtbl6LkyBkUnYt4NbfVtkF8TjHAJ0ST39O4XntFZCmEgfpmaTlG4peIbeviCSt7pOQK0gmoyiju7mERLlNK7h8ZBnSkCA4nbZCTvZBBcdyKOxZCXWd6A4xI8VP5Us19sUr0wZDZD');
            $graphNode = $response->getGraphNode();
            $likes = $graphNode['fan_count'];
        } catch(FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            
            return $likes = 1001;
        } catch(FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            return $likes = 1002;
        }
        return $likes;
    }
}