<?php
namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Form\RegisteredUserType;
use App\Form\UserType;
use App\Notification\ContactNotification;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Doctrine\Common\Persistence\ObjectManager;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints as Assert;

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
        if ($this->getUser()) {
            $this->addFlash('success', 'Vous êtes déjà connecté');
            return $this->redirectToRoute('home');
        }
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
        if ($this->getUser()) {
            $this->addFlash('error', 'Vous avez déjà un compte sur le site. Voulez-vous une mise à jour ?');
            return $this->redirectToRoute('profil.edit');
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $user->setUpdatedAt(new \DateTime());
            $user->setPassword($this->encoder->encodePassword($user, $user->getPassword()));
            $user->setRoles(['ROLE_USER']);
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès');
            return $this->redirectToRoute('login');
        }
        return $this->render('security/signup.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * forgetPassword
     * @Route("/password", name="auth.password")
     * @return void
     */
    public function resetPassword(Request $request, ContactNotification $notification, UserRepository $user)
    {

        if ($request->getMethod() === 'GET') {
            return $this->render('security/password.html.twig');
        }
        if ($this->isCsrfTokenValid('reset-password', $request->get('_token'))) {
            $email = $request->request->get('_email');
            $testUser = $user->findOneBy(['email' => $email]);
            if ($testUser) {
                $token = $this->renewPassword($testUser);
                $notification->sendResetLink($testUser->getEmail(), [
                    'id' => $testUser->getId(),
                    'username' => $testUser->getUsername(),
                    'token' => $token
                ]);
                $this->addFlash('success', 'Un email de réinitialisation vous a été envoyé');
                return $this->redirect($request->getUri());
            }
            // dump($request->request->get('_email'));
            $this->addFlash('error', 'Aucun utilisateur ne correspond à cet email');
            return $this->render('security/password.html.twig');
        } else {
            $this->addFlash('error', 'Invalid Token');
            return $this->render('security/password.html.twig');
        }
    }

    /**
     * resetPasswordAction
     * @Route("/password/reset/{id}/{token}", name="auth.reset", requirements={"id": "\d+"})
     * @return void
     */
    public function resetPasswordAction(Request $request, UserRepository $user) {
        $testUser = $user->findOneBy(['id' => $request->get('id')]);
        if (
            $testUser && 
            $testUser->getPasswordResetAt() !== null &&
            time() - $testUser->getPasswordResetAt()->getTimestamp()
        ) {
            // dump($request->get('id'), $request->get('token'), $elapsedTime);die();
            if ($request->getMethod() === 'GET') {
                return $this->render('security/reset.html.twig');
            } else {
                $pass = $request->request->get('password');
                $confirm = $request->request->get('confirm');
                // dump($pass, $confirm);
                if (!empty($pass) && strlen($pass) > 5) {
                    if ($pass === $confirm ) {
                        $testUser->setPassword($this->encoder->encodePassword($testUser, $pass));
                        $testUser->setPasswordReset(null);
                        $testUser->setPasswordResetAt(null);
                        $testUser->setUpdatedAt(new \DateTime());
                        $this->getDoctrine()->getManager()->persist($testUser);
                        $this->getDoctrine()->getManager()->flush();
                        $this->addFlash('success', 'Votre mot de passe a bien été mis à jour, Veuillez vous connecter');
                        return $this->redirectToRoute('login');
                    } else {
                        $this->addFlash('error', 'Les deux mots de passe ne correspondent pas');
                        return $this->render('security/reset.html.twig');

                    }
                } else {
                    $this->addFlash('error', 'Le mot de passe doit avoir plus de 4 caractères');
                    return $this->render('security/reset.html.twig');
                }
            }
        } else {
            $this->addFlash('error', 'Token invalid');
            return $this->redirectToRoute('auth.password');
        }
    }

    /**
     * show
     * @Route("/profil/{id}", name="profil.show")
     * @param  mixed $user
     * @param  mixed $request
     *
     * @return void
     */
    public function show(User $user, Request $request, PostRepository $postRepository)
    {
        $authorPosts = $postRepository->findPostsByField($request->query->getInt('page', 1), 'author', $user->getId());
        $totalPosts = $postRepository->countAll()[0]['tot'];
        $totalAuthor = $postRepository->countAll($user->getId())[0]['tot'];
        $contribution = $totalAuthor / $totalPosts * 100;
        $stats = [
            'totalAuthor' => $totalAuthor,
            'contribution' => number_format($contribution, 2, ',', ' ')
        ];

        return $this->render('security/show.html.twig', [
            'user' => $user,
            'authorPosts' => $authorPosts,
            'stats' => $stats
        ]);
    }

    /**
     * edit
     * @Route("/profil", name="profil.edit")
     * @param  mixed $request
     *
     * @return void
     */
    public function edit(Request $request)
    {
        if ($this->getUser()) {
            /** @var User  */
            $user = $this->getUser();
            $form = $this->createForm(RegisteredUserType::class, $user);
            if ($request->getMethod() === 'GET') {
                return $this->render('security/edit.html.twig', [
                    'user' => $user,
                    'form' => $form->createView()
                ]);
            }
            $form->handleRequest($request);
            $pass = $request->request->get('password');
            $confirm = $request->request->get('confirm');
            if (($form->isSubmitted() && $form->isValid()) && ($pass === $confirm)) {
                if (!empty($pass)) {
                    $user->setPassword($this->encoder->encodePassword($user, $pass));
                    $this->em->persist($user);
                    $this->em->flush();
                    if ($user->getFilename()) {
                        $targetPath = 'media/users/' .  $user->getFilename();
                        $this->resizeImage($targetPath);
                    }
                    // Comment Avatar
                    $this->em->getRepository(Comment::class)->updateAvatar($user->getEmail(), $user->getFilename());
                    $this->addFlash('success', 'Votre compte a été modifié avec succès');
                    $this->addFlash('success', 'Votre compte a été modifié avec succès, Veuillez vous connecter de nouveau');
                    
                    return $this->redirect('/logout');
                } else {
                    $this->em->persist($user);
                    $this->em->flush();
                    if ($user->getFilename()) {
                        $targetPath = 'media/users/' .  $user->getFilename();
                        $this->resizeImage($targetPath);
                    }
                    // Comment Avatar
                    $this->em->getRepository(Comment::class)->updateAvatar($user->getEmail(), $user->getFilename());
                    $this->addFlash('success', 'Votre compte a été modifié avec succès');
                    
                    return $this->redirectToRoute('home');
                }
            }
            $this->addFlash('error', 'Veuillez remplir les champs avec les bonnes informations');
            return $this->render('security/edit.html.twig', [
                'user' => $user,
                'form' => $form->createView()
            ]);
        }
        $this->addFlash('error', 'Vous ne pouvez pas accéder à cette page, Veuillez d\'abord créer un compte');
        return $this->redirectToRoute('signup');
    }

    private function renewPassword(User $user): string
    {
        $token = Uuid::uuid4()->toString();
        $user->setPasswordReset($token);
        $user->setPasswordResetAt(new \DateTime());
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $token;
    }

    private function resizeImage($targetPath)
    {
        $manager = new ImageManager(['driver' => 'gd']);
        $manager->make($targetPath)->fit(200)->save($targetPath);
    }
}