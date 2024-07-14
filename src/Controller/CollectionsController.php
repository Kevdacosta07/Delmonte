<?php

namespace App\Controller;

use App\Entity\Collections;
use App\Entity\NewsLetter;
use App\Form\CollectionsType;
use App\Form\NewsletterFormType;
use App\Repository\CollectionsRepository;
use App\Repository\NewsLetterRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collections')]
class CollectionsController extends AbstractController
{
    private function generateNewsLetterForm(Request $request): \Symfony\Component\Form\FormInterface
    {
        $newsLetterForm = $this->createForm(NewsletterFormType::class);
        $newsLetterForm->handleRequest($request);

        return $newsLetterForm;
    }

    private function addNewsLetterAdress(EntityManagerInterface $entityManager, NewsLetterRepository $letterRepository, string $adress): void
    {

        if (!filter_var($adress, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash("error", "Veuillez saisir une adresse mail valide !");
            return;
        }

        if (strlen($adress) > 0)
        {

            if ($letterRepository->findOneByEmail($adress))
            {
                $this->addFlash("error", "Cette adresse e-mail est déjà abonnée !");
                return;
            }

            $newsletter = New NewsLetter();

            $newsletter->setEmail($adress);

            $entityManager->persist($newsletter);
            $entityManager->flush();

            $this->addFlash("success", "Vous êtes désormais abonné à la newsletter !");
            return;
        }

        $this->addFlash("error", "Veuillez saisir une adresse mail valide !");

    }

    #[Route('/', name: 'app_collections', methods: ['GET'])]
    public function index(Security $security, CollectionsRepository $collectionsRepository, EntityManagerInterface $entityManager, Request $request, NewsLetterRepository $letterRepository): Response
    {

        $user = $security->getUser();

        if (!$user)
        {
            return $this->redirectToRoute("app_login");
        }

        $newsLetterForm = $this->generateNewsLetterForm($request);

        $currentYear = (new \DateTime())->format('Y');

        if ($newsLetterForm->isSubmitted())
        {
            $data = $newsLetterForm->getData();
            $this->addNewsLetterAdress($entityManager, $letterRepository, $data['email']);
        }

        return $this->render('collections/index.html.twig', [
            'collections' => $collectionsRepository->findAllSortedByYearDesc(),
            "form_newsletter" => $newsLetterForm->createView(),
            "currentYear" => $currentYear,
        ]);
    }

    #[Route('/{id}', name: 'app_collections_show', methods: ['GET'])]
    public function show(Security $security, Collections $collection, EntityManagerInterface $entityManager, ProductsRepository $productsRepository, NewsLetterRepository $letterRepository, Request $request): Response
    {
        $user = $security->getUser();

        $currentYear = (new \DateTime())->format('Y');

        if (!$user)
        {
            return $this->redirectToRoute("app_login");
        }

        if ($currentYear == $collection->getYear() && !$user->isMember())
        {
            return $this->redirectToRoute("app_collections");
        }

        $products = $productsRepository->findByCollectionId($collection->getId());

        $newsLetterForm = $this->generateNewsLetterForm($request);

        if ($newsLetterForm->isSubmitted())
        {
            $data = $newsLetterForm->getData();
            $this->addNewsLetterAdress($entityManager, $letterRepository, $data['email']);
        }

        return $this->render('collections/show.html.twig', [
            'collection' => $collection,
            'products' => $products,
            "form_newsletter" => $newsLetterForm->createView()
        ]);
    }
}
