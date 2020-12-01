<?php
namespace App\Twig;

use App\Repository\PhotoRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use Google_Client;
use Google_Service_YouTube;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SidebarExtension extends AbstractExtension {

    private $videoRepository;

    private $userRepository;

    private $photoRepository;

    private $twig;

    private $cache;

    private $videos;

    private $photos;

    private $users;



    public function __construct(
        VideoRepository $videoRepository,
        PhotoRepository $photoRepository,
        UserRepository $userRepository,
        Environment $twig,
        TagAwareAdapterInterface $cache
    )
    {
        $this->videoRepository = $videoRepository;
        $this->photoRepository = $photoRepository;
        $this->userRepository = $userRepository;
        $this->twig = $twig;
        $this->cache = $cache;
    }
    public function getFunctions(): array
    { 
        return [
            new TwigFunction('videos', [$this, 'getVideos'], ['is_safe' => ['html']]),
            new TwigFunction('users', [$this, 'getUsers'], ['is_safe' => ['html']]),
            new TwigFunction('playlist', [$this, 'getPlaylist'], ['is_safe' => ['html']]),
            new TwigFunction('photos', [$this, 'getPhotos'], ['is_safe' => ['html']])
        ];
    }

    public function getVideos(): string
    {
        return $this->cache->get('videos', function (ItemInterface $item) {
            $item->tag(['videos']);
            return $this->renderVideos();
        });
    }

    public function getUsers(): string
    {
        return $this->cache->get('users', function (ItemInterface $item) {
            $item->tag(['users']);
            return $this->renderUsers();
        });
    }

    public function getPlaylist(): string
    {
        return $this->cache->get('playlist', function (ItemInterface $item) {
            $item->expiresAfter(86400);
            return $this->renderPlaylist();
        });
    }

    public function getPhotos(): string
    {
        return $this->cache->get('photos', function (ItemInterface $item) {
            $item->tag(['photos']);
            return $this->renderPhotos();
        });
    }

    private function getVideo() {
        if ($this->videos === null) {
            $this->videos = $this->videoRepository->findVideos();
        }
        return $this->videos;
    }

    private function getPhoto() {
        if ($this->photos === null) {
            $this->photos = $this->photoRepository->findPhotos();
        }
        return $this->photos;
    }

    private function getUser() {
        if ($this->users === null) {
            $this->users = $this->userRepository->findUsers();
        }
        return $this->users;
    }

    private function renderVideos(): string {
        $videos = $this->getVideo();
        return $this->twig->render('partials/videos.html.twig', [
            'videos' => $videos
        ]);
    }

    private function renderUsers(): string {
        $users = $this->getUser();
        $realUsers = [];
        foreach ($users as $user)
        {
            if (in_array('ROLE_EDITOR', $user->getRoles())) {
                $realUsers[] = $user;
            }
        }
        return $this->twig->render('partials/users.html.twig', [
            'users' => $realUsers
        ]);
    }

    private function renderPhotos(): string {
        $photos = $this->getPhoto();
        return $this->twig->render('partials/photos.html.twig', [
            'photos' => $photos
        ]);
    }

    private function renderPlaylist(): string {
        
        $key = "AIzaSyCPmYWVrORHMnJXXs24V7BkFHHcx9t3T3Q";
        $client = new Google_Client();
        
        $client->setDeveloperKey($key);
        $guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
        $client->setHttpClient($guzzleClient);

        $youtube = new Google_Service_YouTube($client);

        $playlist = $youtube->playlistItems->listPlaylistItems('id,snippet,contentDetails', ['playlistId' => 'UUB0erOivnkO7jkdHFQ_pa7Q', 'maxResults' => 12]);
        return $this->twig->render('partials/playlist.html.twig', [
            'playlist' => $playlist
        ]);
    }
}