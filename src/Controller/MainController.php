<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\MemberRequest;
use App\Entity\NewsLetter;
use App\Form\ContactFormType;
use App\Form\MemberFormType;
use App\Form\NewsletterFormType;
use App\Repository\MemberRequestRepository;
use App\Repository\NewsLetterRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    private function generateNewsLetterForm(Request $request): \Symfony\Component\Form\FormInterface
    {
        $newsLetterForm = $this->createForm(NewsletterFormType::class);
        $newsLetterForm->handleRequest($request);

        return $newsLetterForm;
    }

    private function addNewsLetterAdress(FlasherInterface $flasher, EntityManagerInterface $entityManager, NewsLetterRepository $letterRepository, string $adress): void
    {

        if (!filter_var($adress, FILTER_VALIDATE_EMAIL)) {
            $flasher->error("Veuillez saisir une adresse valide", [], "Error");
            return;
        }

        if (strlen($adress) > 0)
        {

            if ($letterRepository->findOneByEmail($adress))
            {
                $flasher->warning("Cette adresse mail est déjà inscrite", [], "Attention");
                return;
            }

            $newsletter = New NewsLetter();

            $newsletter->setEmail($adress);

            $entityManager->persist($newsletter);
            $entityManager->flush();

            $flasher->success("Vous êtes inscrit à la newsletter", [], "Succès");
            return;
        }

        $flasher->error("Veuillez saisir une adresse mail valide", [], "Erreur");

    }

    #[Route('/8fhskru2jsk', name: 'app_generatedefaultuser')]
    public function generateDefaultUsers(UserPasswordHasherInterface  $passwordHasher, UserRepository $userRepository, FlasherInterface $flasher, EntityManagerInterface $entityManager): Response
    {
        $userExist = $userRepository->findOneByEmail("admin@mail.ch");

        if ($userExist)
        {
            return $this->redirectToRoute("app_accueil");
        }

        $user = new User();
        $userAdmin = new User();

        $user->setFirstName("Anais");
        $user->setLastName("Therum");
        $user->setEmail("user@mail.ch");
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user,
                "password"
            )
        );
        $user->setMember(false);
        $user->setRoles(['ROLE_USER']);
        $user->setAdmin(false);

        $userAdmin->setFirstName("Leo");
        $userAdmin->setEmail("admin@mail.ch");
        $userAdmin->setLastName("Harim");
        $userAdmin->setPassword(
            $passwordHasher->hashPassword(
                $userAdmin,
                "password"
            )
        );
        $userAdmin->setMember(false);
        $userAdmin->setRoles(['ROLE_USER']);
        $userAdmin->setAdmin(true);

        $entityManager->persist($user);
        $entityManager->persist($userAdmin);
        $entityManager->flush();

        $flasher->success("Comptes créés", [], "Succès");

        return $this->redirectToRoute("app_accueil");
    }

    #[Route('/', name: 'app_accueil')]
    public function index(FlasherInterface $flasher, Request $request, EntityManagerInterface $entityManager, NewsLetterRepository $letterRepository): Response
    {
        $newsLetterForm = $this->generateNewsLetterForm($request);

        $currentYear = (new \DateTime())->format('Y');

        if ($newsLetterForm->isSubmitted())
        {
            $data = $newsLetterForm->getData();
            $this->addNewsLetterAdress($flasher, $entityManager, $letterRepository, $data['email']);

            return $this->redirectToRoute("app_accueil");
        }

        return $this->render('main/index.html.twig', [
            "form_newsletter" => $newsLetterForm->createView(),
            'currentYear' => $currentYear
        ]);
    }

    #[Route('/biographie', name: 'app_biographie')]
    public function biographie(): Response
    {
        return $this->render('main/biographie.html.twig', [

        ]);
    }

    #[Route('/biographie/details', name: 'app_biographie_details')]
    public function biographie_moreinfo(): Response
    {
        return $this->render('main/biographie_details.html.twig');
    }

    #[Route('/membres', name: 'app_membres')]
    public function membres(FlasherInterface $flasher, Security $security, MemberRequestRepository $memberRequestRepository, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager, NewsLetterRepository $letterRepository): Response
    {
        $utilisateur = $security->getUser();

        if (!$utilisateur)
        {
            return $this->redirectToRoute("app_login");
        }
        $user = $userRepository->findOneByEmail($utilisateur->getEmail());

        $isUserMember = false;

        if ($memberRequestRepository->findOneByEmail($utilisateur->getEmail()))
        {
            $isUserMember = true;
        }

        else if ($user->isMember())
        {
            $isUserMember = true;
        }

        $newsLetterForm = $this->generateNewsLetterForm($request);

        if ($newsLetterForm->isSubmitted())
        {
            $data = $newsLetterForm->getData();
            $this->addNewsLetterAdress($flasher, $entityManager, $letterRepository, $data['email']);
            return $this->redirectToRoute("app_membres");
        }

        return $this->render('main/membres.html.twig', [
            "form_newsletter" => $newsLetterForm->createView(),
            "isUserMember" => $isUserMember
        ]);
    }

    #[Route('/membres/adhesion', name: 'app_membres_adhesion')]
    public function membres_adhesion(MailerInterface $mailer, FlasherInterface $flasher, Request $request, UserRepository $userRepository, MemberRequestRepository $memberRequestRepository, EntityManagerInterface $entityManager, NewsLetterRepository $letterRepository, Security $security): Response
    {

        $utilisateur = $security->getUser();

        if ($utilisateur)
        {
            if ($memberRequestRepository->findOneByEmail($utilisateur->getEmail()))
            {
                return $this->redirectToRoute("app_membres");
            }

            $user = $userRepository->findOneByEmail($utilisateur->getEmail());

            if ($user->isMember())
            {
                return $this->redirectToRoute("app_membres");
            }

        }

        else
        {
            return $this->redirectToRoute("app_login");
        }

        $form = $this->createForm(MemberFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            $email = $utilisateur->getEmail();
            $lastname = $utilisateur->getLastname();
            $firstname = $utilisateur->getFirstname();

            // Créée l'e-mail
            $mail = (new Email())
                ->from($email)
                ->to("admin@delmonte.ch")
                ->subject("Demande d'adhesion membre")
                ->text("Vous avez reçu une demande d'adhesion de la part de : $lastname $firstname");

            try
            {
                // Envoie l'e-mail
                $mailer->send($mail);
            } catch (TransportExceptionInterface $e)
            {
                $flasher->error("Une erreur s'est produite lors de l'envoi de votre message", [], "Erreur");
            }

            $memberRequest = new MemberRequest();

            $memberRequest->setEmail($email);
            $memberRequest->setFirstname($firstname);
            $memberRequest->setLastname($lastname);
            $memberRequest->setCreatedAt(new \DateTimeImmutable());

            $flasher->success("Votre demande a été envoyée", [], "Succès");

            $entityManager->persist($memberRequest);
            $entityManager->flush();

            return $this->redirectToRoute("app_profile");
        }

        $newsLetterForm = $this->generateNewsLetterForm($request);

        if ($newsLetterForm->isSubmitted())
        {
            $data = $newsLetterForm->getData();
            $this->addNewsLetterAdress($flasher, $entityManager, $letterRepository, $data['email']);
            return $this->redirectToRoute("app_contact");
        }

        return $this->render('main/membres_adhesion.html.twig', [
            "form_newsletter" => $newsLetterForm->createView(),
            "form_membreadh" => $form->createView()
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(FlasherInterface $flasher, Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, NewsLetterRepository $letterRepository): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);


        $newsLetterForm = $this->generateNewsLetterForm($request);

        if ($newsLetterForm->isSubmitted())
        {
            $data = $newsLetterForm->getData();
            $this->addNewsLetterAdress($flasher, $entityManager, $letterRepository, $data['email']);
            return $this->redirectToRoute("app_contact");
        }

        if ($form->isSubmitted())
        {
            $contact = $form->getData();

            $email = (new Email())
                ->from($contact['email'])
                ->to('admin@delmonte.ch')
                ->subject('Concerne : ' . $contact['concerne'])
                ->text($contact['message']);

            try {
                $mailer->send($email);
                $flasher->success("Votre message a été envoyé", [], "Succès");
            } catch (TransportExceptionInterface $e) {
                $flasher->error("Une erreur a eu lieu avec l'envoie de votre message", [], "Erreur");
            }
            return $this->redirectToRoute("app_contact");
        }

        return $this->render('main/contact.html.twig', [
            'contactForm' => $form->createView(),
            'form_newsletter' => $newsLetterForm->createView()
        ]);
    }
}
