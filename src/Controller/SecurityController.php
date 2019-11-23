<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    private $repository;

    private $em;

    private $encoder;

    public function __construct(
        UserRepository $repository, 
        ObjectManager $em,
        UserPasswordEncoderInterface $encoder
    )
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->encoder = $encoder;
    }
    /**
     * login
     * @Route("/login", name="login")
     * @return void
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * login
     * @Route("/signup", name="signup")
     * @return void
     */
    public function signup(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès');
            return $this->redirectToRoute('home');
        }
        return $this->render('security/signup.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}