<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Notification\ContactNotification;
use App\Repository\PhotoRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
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
    public function index(UserRepository $userRepository, VideoRepository $videoRepository, PhotoRepository $photoRepository, TagAwareAdapterInterface $cache, Request $request, ContactNotification $notification): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notification->notify($contact);
            $this->addFlash('success', 'Votre email a bien été envoyé');
            return $this->redirect("/#contact");
            // return $this->redirectToRoute('home' );
        }
        return $this->render('views/home.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * contactUs
     *
     * @param  mixed $notification
     * @param  mixed $request
     * @Route("/contact", name="home.contact")
     * @return void
     */
    public function contactUs(ContactNotification $notification, Request $request)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notification->notify($contact);
            $this->addFlash('success', 'Votre email a bien été envoyé');
            return $this->redirectToRoute('home');
        }

        return $this->render('contact/contact.html.twig', [
            'form' => $form->createView()
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
        
        $playlist = $youtube->playlistItems->listPlaylistItems('id,snippet,contentDetails', ['playlistId' => 'UUB0erOivnkO7jkdHFQ_pa7Q', 'maxResults' => 12]);
        // $playlist = $youtube->playlistItems->listPlaylistItems('id,snippet,contentDetails', ['playlistId' => 'UU4_mlXKezTbWDxrLihjvxNw', 'maxResults' => 12]);
        // $response = $youtube->search->listSearch('id,snippet', ['q' => 'mjcn', 'order' => 'relevance', 'maxResults' => 12, 'type' => 'video']);
        // $first = $youtube->videos->listVideos('id,snippet,contentDetails', ['id' => $response['items'][0]['id']['videoId']])['items'][0];

        return $this->render('video/index.html.twig', compact('playlist'));
    }

}