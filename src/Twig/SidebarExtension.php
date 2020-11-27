<?php
namespace App\Twig;

use Abraham\TwitterOAuth\TwitterOAuth;
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
        PubRepository $pubRepository,
        Environment $twig,
        TagAwareAdapterInterface $cache,
        CacheInterface $socialCache
    )
    {
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
}