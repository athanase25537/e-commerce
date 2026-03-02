<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ReviewRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/review')]
#[IsGranted('ROLE_USER')]
final class ReviewController extends AbstractController
{
    #[Route('/product/{id}', name: 'app_review_create', methods: ['POST'])]
    public function create(
        Request $request,
        Product $product,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $existing = $reviewRepository->findOneByProductAndUser($product->getId(), $user->getId());
        if ($existing) {
            $this->addFlash('info', 'Vous avez deja laisse un avis pour ce produit.');
            return $this->redirectToRoute('app_shop_show', ['id' => $product->getId()]);
        }

        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setProduct($product);
            $review->setUser($user);
            $review->setIsApproved(true);
            $review->setCreatedAt(new DateTimeImmutable());

            $entityManager->persist($review);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre avis !');
        } else {
            $this->addFlash('warning', 'Avis invalide.');
        }

        return $this->redirectToRoute('app_shop_show', ['id' => $product->getId()]);
    }
}
