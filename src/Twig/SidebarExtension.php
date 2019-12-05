<?php
namespace App\Twig;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SidebarExtension extends AbstractExtension {

    private $postRepository;

    private $categoryRepository;

    private $twig;

    private $cache;

    public function __construct(
        PostRepository $postRepository,
        CategoryRepository $categoryRepository,
        Environment $twig,
        TagAwareAdapterInterface $cache
    )
    {
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
        $this->twig = $twig;
        $this->cache = $cache;
    }
    public function getFunctions(): array
    { 
        return [
            new TwigFunction('sidebar', [$this, 'getSidebar'], ['is_safe' => ['html']]),
            new TwigFunction('footer', [$this, 'getFooter'], ['is_safe' => ['html']]),
            new TwigFunction('lastPosts', [$this, 'getLastPosts'], ['is_safe' => ['html']]),
            new TwigFunction('emergency', [$this, 'getEmergency'], ['is_safe' => ['html']])
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

    public function getLastPosts()
    {
        return $this->cache->get('lastPosts', function (ItemInterface $item) {
            $item->tag(['lastPosts']);
            return $this->renderLastPosts();
        });
    }

    public function getEmergency()
    {
        return $this->cache->get('emergency', function (ItemInterface $item) {
            $item->tag(['lastPosts']);
            return $this->renderEmergency();
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

    private function renderLastPosts(): string {
        $lastPosts = $this->postRepository->findLastPosts();
        return $this->twig->render('partials/lastposts.html.twig', [
            'lastPosts' => $lastPosts
        ]);
    }

    private function renderEmergency(): string {
        $lastPosts = $this->postRepository->findLastestPosts();
        return $this->twig->render('partials/emergency.html.twig', [
            'lastPosts' => $lastPosts
        ]);
    }
}