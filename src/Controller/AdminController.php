<?php

namespace App\Controller;

use App\Entity\Collections;
use App\Entity\Products;
use App\Entity\User;
use App\Form\CollectionsType;
use App\Form\NewsletterFormType;
use App\Form\NewsletterSendingType;
use App\Form\ProductsType;
use App\Form\UserType;
use App\Repository\CollectionsRepository;
use App\Repository\MemberRequestRepository;
use App\Repository\NewsLetterRepository;
use App\Repository\ProductsRepository;
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

#[Route('/admin')]
class AdminController extends AbstractController
{
    private function generateNewsLetterForm(Request $request): \Symfony\Component\Form\FormInterface
    {
        $newsLetterForm = $this->createForm(NewsletterFormType::class);
        $newsLetterForm->handleRequest($request);

        return $newsLetterForm;
    }

    private function checkAdminUser(Security $security): bool
    {
        $user = $security->getUser();

        if (!$user)
        {
            return false;
        }

        if (!$user->isAdmin())
        {
            return false;
        }

        return true;
    }

    #[Route('/collections', name: 'app_admin_collections')]
    public function collections(Security $security, EntityManagerInterface $entityManager, Request $request, CollectionsRepository $collectionsRepository): Response
    {
        #Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        $collections = $collectionsRepository->findAllSortedByYear();

        return $this->render('admin/collections.html.twig', [
            "collections" => $collections
        ]);
    }

    #[Route('/collections/add', name: 'app_admin_collections_add', methods: ['POST', 'GET'])]
    public function collections_add(FlasherInterface $flasher, Security $security, CollectionsRepository $collectionsRepository, EntityManagerInterface $entityManager, Request $request, NewsLetterRepository $letterRepository): Response
    {
        # Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        $coll = new Collections();

        $collections = $collectionsRepository->findAll();

        $form = $this->createForm(CollectionsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $year = $form->get('year')->getData();

            $coll->setYear($year);

            if ($collectionsRepository->findOneByYear($year))
            {
                $flasher->error("Cette année est déjà utilisée.");
                return $this->redirectToRoute("app_admin_collections_add");
            }

            $entityManager->persist($coll);
            $entityManager->flush();

            return $this->redirectToRoute("app_admin_collections");
        }

        return $this->render('admin/collections_add.html.twig', [
            "collections" => $collections,
            "form" => $form->createView()
        ]);
    }

    #[Route('/collections/{id}}', name: 'app_admin_collections_delete', methods: ['POST'])]
    public function collections_delete(FlasherInterface $flasher, Security $security, Request $request, ProductsRepository $productsRepository, Collections $collection, EntityManagerInterface $entityManager): Response
    {
        # Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        if ($this->isCsrfTokenValid('delete'.$collection->getId(), $request->getPayload()->getString('_token'))) {
            $productsRepository->deleteByCollectionId($collection->getId());
            $entityManager->remove($collection);
            $entityManager->flush();
            $flasher->success("La collection a été éditée", [], "Succès");
        }

        return $this->redirectToRoute('app_admin_collections', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/collections/edit/{id}}', name: 'app_admin_collections_edit', methods: ['GET', 'POST'])]
    public function collections_edit(FlasherInterface $flasher, Security $security, Request $request, Collections $collection, EntityManagerInterface $entityManager): Response
    {
        # Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        $form = $this->createForm(CollectionsType::class, $collection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $flasher->success("La collection a été éditée", [], "Succès");


            return $this->redirectToRoute('app_admin_collections', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/collections_edit.html.twig', [
            'collection' => $collection,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/collections/{id}', name: 'app_admin_collections_show', methods: ['GET'])]
    public function collections_show(Collections $collection, Security $security, ProductsRepository $productsRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        # Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        $products = $productsRepository->findByCollectionId($collection->getId());

        return $this->render('admin/collections_show.html.twig', [
            'collection' => $collection,
            'id' => $collection->getId(),
            "products" => $products
        ]);

    }

    #[Route('/products/add/{id}', name: 'app_admin_products_add', methods: ['GET', 'POST'])]
    public function products_add(FlasherInterface $flasher, Collections $collection, Security $security, EntityManagerInterface $entityManager, Request $request): Response
    {

        # Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        $product = new Products();

        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

            $product->setCollectionId($collection->getId());

            $entityManager->persist($product);
            $entityManager->flush();

            $flasher->success("Votre produit a été créé", [], "Succès");

            return $this->redirectToRoute("app_admin_collections");
        }

        return $this->render('admin/products_add.html.twig', [
            "form" => $form->createView()
        ]);

    }

    #[Route('/products/{id}}', name: 'app_admin_products_delete', methods: ['POST'])]
    public function products_delete(Security $security, Request $request, Products $product, EntityManagerInterface $entityManager): Response
    {
        # Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_collections', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/products/edit/{id}}', name: 'app_admin_products_edit', methods: ['GET', 'POST'])]
    public function products_edit(FlasherInterface $flasher, Security $security, Request $request, Products $product, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security)) {
            return $this->redirectToRoute("app_login");
        }

        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($product);
            $entityManager->flush();

            $flasher->success("Votre produit a été modifié", [], "Succès");

            return $this->redirectToRoute("app_admin_collections_show", ['id' => $product->getCollectionId()]);
        }

        return $this->render('admin/products_edit.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/users', name: 'app_admin_users', methods: ['GET', 'POST'])]
    public function members_admin(Security $security, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security)) {
            return $this->redirectToRoute("app_login");
        }

        $users = $userRepository->findAll();


        return $this->render('admin/users.html.twig', [
            "users" => $users
        ]);
    }

    #[Route('/users/{id}}', name: 'app_admin_users_delete', methods: ['POST'])]
    public function users_delete(FlasherInterface $flasher, Security $security, Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        # Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $flasher->success("L'utilisateur a été supprimé", [], "Succès");
        }

        return $this->redirectToRoute('app_admin_users', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/users/add', name: 'app_admin_users_add', methods: ['GET', 'POST'])]
    public function users_add(FlasherInterface $flasher, Security $security, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        # Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security))
        {
            return $this->redirectToRoute("app_login");
        }

        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $passwordData = $form->get('password')->getData();

            if (!empty($passwordData)) {
                // Hasha
                $hashedPassword = $passwordHasher->hashPassword($user, $passwordData);
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_USER']);

                $entityManager->persist($user);
                $entityManager->flush();

                $flasher->success("L'utilisateur a été créé", [], "Succès");

                return $this->redirectToRoute("app_admin_users");
            }

            else
            {
                $flasher->error("Veuillez saisir un mot de passe", [], "Erreur");
            }
        }

        return $this->render('admin/users_add.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/users/edit/{id}', name: 'app_admin_users_edit', methods: ['GET', 'POST'])]
    public function users_edit(FlasherInterface $flasher, Security $security, UserPasswordHasherInterface $passwordHasher, Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        // Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security)) {
            return $this->redirectToRoute("app_login");
        }

        // Sauvegarde le mot de passe actuel
        $currentPassword = $user->getPassword();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le champ mot de passe est vide, conserver le mot de passe actuel
            $passwordData = $form->get('password')->getData();
            if (empty($passwordData)) {
                // Remettre l'ancien mot de passe
                $user->setPassword($currentPassword);
            } else {
                // Encoder le nouveau mot de passe
                $encodedPassword = $passwordHasher->hashPassword($user, $passwordData);
                $user->setPassword($encodedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $flasher->success("L'utilisateur a été édité", [], "Succès");

            return $this->redirectToRoute("app_admin_users");
        }

        return $this->render('admin/users_edit.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/newsletter', name: 'app_admin_newsletter_handler', methods: ['GET', 'POST'])]
    public function newsletter_handler(FlasherInterface $flasher, Security $security, MailerInterface $mailer, Request $request, NewsLetterRepository $letterRepository): Response
    {
        // Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security)) {
            return $this->redirectToRoute("app_login");
        }

        $form = $this->createForm(NewsletterSendingType::class);
        $form->handleRequest($request);

        // Récupère les adresses e-mail des abonnés
        $targetMails = $letterRepository->findAll();
        $emailAddresses = array_map(fn($subscriber) => $subscriber->getEmail(), $targetMails);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Crée l'e-mail
            $email = (new Email())
                ->from("contact@delmonte.ch")
                ->to(...$emailAddresses)
                ->subject($data['subject'])
                ->text($data['message']);

            try {
                // Envoie l'e-mail
                $mailer->send($email);
                $flasher->success("Newsletter envoyée !", [], "Succès");
            } catch (TransportExceptionInterface $e) {
                $flasher->error("Une erreur s'est produite lors de l'envoi de votre message", [], "Erreur");
            }

            return $this->redirectToRoute("app_admin_newsletter_handler");
        }

        return $this->render('admin/newsletter_handler.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/member/request', name: 'app_admin_memberrequest', methods: ['GET', 'POST'])]
    public function membre_request(FlasherInterface $flasher, Security $security, MailerInterface $mailer, Request $request, MemberRequestRepository $memberRequestRepository): Response
    {
        // Vérifie si l'utilisateur est admin
        if (!$this->checkAdminUser($security)) {
            return $this->redirectToRoute("app_login");
        }

        $form = $this->createForm(NewsletterSendingType::class);
        $form->handleRequest($request);

        // Récupère les adresses e-mail des abonnés
        $targetMails = $memberRequestRepository->findAll();
        $emailAddresses = array_map(fn($subscriber) => $subscriber->getEmail(), $targetMails);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Créée l'e-mail
            $email = (new Email())
                ->from("contact@delmonte.ch")
                ->to($emailAddresses)
                ->subject($data['subject'])
                ->text($data['message']);

            try {
                // Envoie l'e-mail
                $mailer->send($email);
                $flasher->success("Newsletter envoyée !", [], "Succès");
            } catch (TransportExceptionInterface $e) {
                $flasher->error("Une erreur s'est produite lors de l'envoi de votre message", [], "Erreur");
            }

            return $this->redirectToRoute("app_admin_newsletter_handler");
        }

        return $this->render('admin/newsletter_handler.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
