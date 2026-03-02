<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/shop')]
final class ShopController extends AbstractController
{
    #[Route('', name: 'app_shop_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        SubCategoryRepository $subCategoryRepository
    ): Response {
        $query = trim((string) $request->query->get('q', ''));
        $categoryId = $request->query->getInt('category') ?: null;
        $subCategoryId = $request->query->getInt('sub') ?: null;

        $products = $productRepository->findForStore($query ?: null, $categoryId, $subCategoryId);
        $categories = $categoryRepository->findAll();
        $subCategories = $categoryId
            ? $subCategoryRepository->findBy(['category' => $categoryId])
            : $subCategoryRepository->findAll();

        return $this->render('shop/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'sub_categories' => $subCategories,
            'selected_category' => $categoryId,
            'selected_sub_category' => $subCategoryId,
            'search_query' => $query,
        ]);
    }

    #[Route('/{id}', name: 'app_shop_show', methods: ['GET'])]
    public function show(Product $product, ReviewRepository $reviewRepository): Response
    {
        $review = new Review();
        $reviewForm = $this->createForm(ReviewType::class, $review);
        $reviews = $reviewRepository->findBy(
            ['product' => $product, 'isApproved' => true],
            ['createdAt' => 'DESC']
        );

        return $this->render('shop/show.html.twig', [
            'product' => $product,
            'review_form' => $reviewForm,
            'reviews' => $reviews,
        ]);
    }
}
