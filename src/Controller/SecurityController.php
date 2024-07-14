<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CollectionsType;
use App\Form\RegistrationFormType;
use App\Form\User2Type;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    #[Route(path: '/login', name: 'app_login')]
    public function login(Security $security, AuthenticationUtils $authenticationUtils): Response
    {
        $user = $security->getUser();

        if ($user)
        {
            return $this->redirectToRoute("app_profile");
        }

        // get the security error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(FlasherInterface $flasher, Request $request, Security $security, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $utilisateur = $security->getUser();

        if ($utilisateur) {
            return $this->redirectToRoute("app_profile");
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $password = $form->get("plainPassword")->getData();
            $lastname = $form->get("lastname")->getData();
            $firstname = $form->get("firstname")->getData();
            $email = $form->get("email")->getData();
            $existingUser = $userRepository->findOneByEmail($email);

            if ($existingUser)
            {
                $flasher->error("Cette adresse e-mail est déjà utilisée", [], "Erreur");
            }

            elseif (strlen($lastname) == 0 || strlen($password) == 0 || strlen($firstname) == 0 || strlen($email) == 0)
            {
                $flasher->error("Veuillez remplir tous les champs", [], "Erreur");
            }

            elseif (strlen($password) < 8)
            {
                $flasher->error("Votre mot de passe doit contenir 8 caractères", [], "Erreur");
            }
            else {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $password
                    )
                );

                $user->setMember(false);

                $user->setRoles(['ROLE_USER']);

                $user->setAdmin(false);

                $entityManager->persist($user);
                $entityManager->flush();

                $flasher->success("Votre compte a été créé", [], "Succès");

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute("app_accueil");
    }

    #[Route(path: '/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(FlasherInterface $flasher, Request $request, UserPasswordHasherInterface $passwordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute("app_login");
        }

        $form = $this->createForm(User2Type::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPassword = $form->get('password')->getData();

            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $flasher->success("Votre profil a été modifié", [], "Succès");

            return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('security/profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
