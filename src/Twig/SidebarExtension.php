<?php
namespace App\Twig;

use Abraham\TwitterOAuth\TwitterOAuth;
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

    private $posts;

    private $categories;

    private $pub;

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
            new TwigFunction('menu', [$this, 'getMenu'], ['is_safe' => ['html']]),
            new TwigFunction('submenu', [$this, 'getSubmenu'], ['is_safe' => ['html']]),
            new TwigFunction('reports', [$this, 'getReports'], ['is_safe' => ['html']])
        ];
    }

    public function getSidebar(): string
    {
        return $this->cache->get('sidebar', function (ItemInterface $item) {
            $item->tag(['topPosts']);
            return $this->renderSidebar();
        });
    }

    public function getFooter(): string
    {
        return $this->cache->get('footer', function (ItemInterface $item) {
            $item->tag(['posts', 'categories']);
            return $this->renderFooter();
        });
    }

    public function getTopPub(): string
    {
        return $this->cache->get('topPub', function (ItemInterface $item) {
            $item->tag(['topPub']);
            return $this->renderTopPub();
        });
    }

    public function getPub(): string
    {
        return $this->cache->get('pub', function (ItemInterface $item) {
            $item->tag(['pub']);
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

    public function getMenu(): string
    {
        return $this->cache->get('menu', function (ItemInterface $item) {
            $item->tag(['categories']);
            return $this->renderMenu();
        });
    }

    public function getSubmenu(): string
    {
        return $this->cache->get('submenu', function (ItemInterface $item) {
            $item->tag(['categories']);
            return $this->renderSubmenu();
        });
    }

    public function getReports(): string
    {
        return $this->cache->get('reports', function (ItemInterface $item) {
            $item->tag(['categories']);
            return $this->renderReports();
        });
    }

    private function getPosts() {
        if ($this->posts === null) {
            $this->posts = $this->postRepository->findForSidebar();
        }
        return $this->posts;
    }

    private function getAd() {
        if ($this->pub === null) {
            $this->pub = $this->pubRepository->findOneBy(['promo' => 1]);
        }
        return $this->pub;
    }

    private function getCategories() {
        if ($this->categories === null) {
            $this->categories = $this->categoryRepository->findAll();
        }
        return $this->categories;
    }

    private function renderSidebar(): string {
        $topPosts = $this->getPosts();
        return $this->twig->render('partials/sidebar.html.twig', [
            'topPosts' => $topPosts
        ]);
    }

    private function renderFooter(): string {
        $posts = $this->getPosts();
        $popularPosts = $this->postRepository->findPopularPosts();
        $categories = $this->categoryRepository->findForFooter();
        return $this->twig->render('partials/footer.html.twig', [
            'posts' => $posts,
            'popularPosts' => $popularPosts,
            'categories' => $categories
        ]);
    }

    private function renderTopPub(): string {
        $topPub = $this->getAd();
        return $this->twig->render('partials/topPub.html.twig', [
            'topPub' => $topPub
        ]);
    }

    private function renderPub(): string {
        $pub = $this->getAd();
        return $this->twig->render('partials/pub.html.twig', [
            'pub' => $pub
        ]);
    }

    private function renderMenu(): string {
        $categories = $this->getCategories();
        return $this->twig->render('partials/menu.html.twig', [
            'categories' => $categories
        ]);
    }

    private function renderReports(): string {
        $categories = $this->getCategories();
        return $this->twig->render('partials/reports.html.twig', [
            'categories' => $categories
        ]);
    }

    private function renderSubmenu(): string {
        $categories = $this->getCategories();
        return $this->twig->render('partials/submenu.html.twig', [
            'categories' => $categories
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
        $oauth = new TwitterOAuth("cWAgBW4u1vFvVII7xP29TOSyO", "b3fdKw1fFSwzxpFHpPzQgcP5aHH84ZeWV0fbdEm7gHoj0FyP9x");
        $accessToken = $oauth->oauth2('oauth2/token', ['grant_type' => 'client_credentials']);

        $twitter = new TwitterOAuth("cWAgBW4u1vFvVII7xP29TOSyO", "b3fdKw1fFSwzxpFHpPzQgcP5aHH84ZeWV0fbdEm7gHoj0FyP9x", null, $accessToken->access_token);
        $user = $twitter->get('users/show', [
            'screen_name' => 'GrandsLacsNews'
        ]);
        return (int)$user->followers_count;
    }

    private function getLikes()
    {
        $fb = new Facebook([
            'app_id' => '454671828554424',
            'app_secret' => '826e36db816f2daa40a9fe653f6a5e68',
            'default_graph_version' => 'v2.10',
        ]);
        
        try {
            $response = $fb->get('/108304060702176?fields=fan_count&access_token=EAAGdhYjUkrgBAH07rbdfSJRCPUKq2tHYLnCwLsbVQN7WVkzhyIHrrPNcCx8hUjKkgalGC6qeZC5peR3Uovl6trry6ZBMCnt7D5GK11tRAzM6Yj8lkDhTpV8X97q8CyP7tW6lXMZBADVX8ca4uAO442kVwmSp9MQ5FxjBGSrPgZDZD', 'EAAGdhYjUkrgBAH07rbdfSJRCPUKq2tHYLnCwLsbVQN7WVkzhyIHrrPNcCx8hUjKkgalGC6qeZC5peR3Uovl6trry6ZBMCnt7D5GK11tRAzM6Yj8lkDhTpV8X97q8CyP7tW6lXMZBADVX8ca4uAO442kVwmSp9MQ5FxjBGSrPgZDZD');
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