<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private const CART_KEY = 'cart';
    private const COUPON_KEY = 'cart_coupon';

    private SessionInterface $session;

    public function __construct(RequestStack $requestStack)
    {
        $session = $requestStack->getSession();
        if (!$session) {
            throw new \RuntimeException('Session is not available. Ensure sessions are enabled.');
        }

        $this->session = $session;
    }

    /**
     * @return array<int, int>
     */
    public function getCart(): array
    {
        return $this->session->get(self::CART_KEY, []);
    }

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;

        if ($cart[$productId] <= 0) {
            unset($cart[$productId]);
        }

        $this->session->set(self::CART_KEY, $cart);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->getCart();
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        $this->session->set(self::CART_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        $this->session->set(self::CART_KEY, $cart);
    }

    public function clear(): void
    {
        $this->session->remove(self::CART_KEY);
        $this->session->remove(self::COUPON_KEY);
    }

    public function getTotalQuantity(): int
    {
        return array_sum($this->getCart());
    }

    public function setCouponCode(?string $code): void
    {
        if ($code === null || $code === '') {
            $this->session->remove(self::COUPON_KEY);
            return;
        }

        $this->session->set(self::COUPON_KEY, $code);
    }

    public function getCouponCode(): ?string
    {
        $code = $this->session->get(self::COUPON_KEY);
        return is_string($code) && $code !== '' ? $code : null;
    }

    public function clearCoupon(): void
    {
        $this->session->remove(self::COUPON_KEY);
    }

    /**
     * @return array{items: array<int, array{product: object, quantity: int, lineTotal: int}>, total: int}
     */
    public function getDetails(ProductRepository $productRepository): array
    {
        $cart = $this->getCart();
        if ($cart === []) {
            return ['items' => [], 'total' => 0];
        }

        $products = $productRepository->findBy(['id' => array_keys($cart)]);
        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product->getId()] = $product;
        }

        $items = [];
        $total = 0;
        foreach ($cart as $productId => $quantity) {
            if (!isset($productsById[$productId])) {
                continue;
            }

            $product = $productsById[$productId];
            $lineTotal = $product->getPrice() * $quantity;
            $total += $lineTotal;
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'lineTotal' => $lineTotal,
            ];
        }

        return [
            'items' => $items,
            'total' => $total,
        ];
    }
}
