<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
final class CartController extends AbstractController
{
    #[Route('', name: 'app_cart_index', methods: ['GET'])]
    public function index(
        CartService $cartService,
        ProductRepository $productRepository,
        CouponRepository $couponRepository
    ): Response
    {
        $cartDetails = $cartService->getDetails($productRepository);
        $couponCode = $cartService->getCouponCode();
        $coupon = null;
        $discount = 0;
        if ($couponCode) {
            $coupon = $couponRepository->findActiveByCode($couponCode);
        }

        if ($coupon && $coupon->isActive()) {
            $now = new \DateTimeImmutable();
            $isWithinDates = (!$coupon->getStartsAt() || $coupon->getStartsAt() <= $now)
                && (!$coupon->getEndsAt() || $coupon->getEndsAt() >= $now);
            $isWithinUsage = $coupon->getUsageLimit() === null || $coupon->getUsedCount() < $coupon->getUsageLimit();

            if ($isWithinDates && $isWithinUsage) {
                if ($coupon->getType() === 'percentage') {
                    $discount = (int) round($cartDetails['total'] * ($coupon->getValue() / 100));
                } else {
                    $discount = $coupon->getValue();
                }
                $discount = min($discount, $cartDetails['total']);
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartDetails['items'],
            'total' => $cartDetails['total'],
            'coupon' => $coupon,
            'discount' => $discount,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Request $request, Product $product, CartService $cartService): Response
    {
        if (!$this->isCsrfTokenValid('cart_add_' . $product->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_shop_show', ['id' => $product->getId()]);
        }

        $quantity = max(1, (int) $request->request->get('quantity', 1));
        $stock = $product->getStock();
        if ($stock !== null && $stock <= 0) {
            $this->addFlash('warning', 'Produit en rupture de stock.');
            return $this->redirectToRoute('app_shop_show', ['id' => $product->getId()]);
        }

        if ($stock !== null && $quantity > $stock) {
            $quantity = $stock;
        }

        $cartService->add($product->getId(), $quantity);
        $this->addFlash('success', 'Produit ajoute au panier.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Request $request, Product $product, CartService $cartService): Response
    {
        if (!$this->isCsrfTokenValid('cart_update_' . $product->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $quantity = max(0, (int) $request->request->get('quantity', 0));
        $stock = $product->getStock();
        if ($stock !== null && $quantity > $stock) {
            $quantity = $stock;
            $this->addFlash('warning', 'Quantite ajustee au stock disponible.');
        }

        $cartService->update($product->getId(), $quantity);
        $this->addFlash('success', 'Panier mis a jour.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Request $request, Product $product, CartService $cartService): Response
    {
        if (!$this->isCsrfTokenValid('cart_remove_' . $product->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $cartService->remove($product->getId());
        $this->addFlash('success', 'Produit retire du panier.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/coupon', name: 'app_cart_coupon', methods: ['POST'])]
    public function applyCoupon(Request $request, CartService $cartService, CouponRepository $couponRepository): Response
    {
        if (!$this->isCsrfTokenValid('cart_coupon', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $code = strtoupper(trim((string) $request->request->get('code')));
        if ($code === '') {
            $cartService->clearCoupon();
            $this->addFlash('info', 'Code promo retire.');
            return $this->redirectToRoute('app_cart_index');
        }

        $coupon = $couponRepository->findActiveByCode($code);
        if (!$coupon || !$coupon->isActive()) {
            $this->addFlash('warning', 'Code promo invalide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $cartService->setCouponCode($code);
        $this->addFlash('success', 'Code promo applique.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/coupon/remove', name: 'app_cart_coupon_remove', methods: ['POST'])]
    public function removeCoupon(Request $request, CartService $cartService): Response
    {
        if (!$this->isCsrfTokenValid('cart_coupon_remove', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $cartService->clearCoupon();
        $this->addFlash('info', 'Code promo retire.');

        return $this->redirectToRoute('app_cart_index');
    }
}
