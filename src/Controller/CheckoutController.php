<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Form\CheckoutType;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use App\Service\CartService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
final class CheckoutController extends AbstractController
{
    #[Route('', name: 'app_checkout', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        CartService $cartService,
        ProductRepository $productRepository,
        CouponRepository $couponRepository,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ): Response {
        $cartDetails = $cartService->getDetails($productRepository);
        if ($cartDetails['items'] === []) {
            $this->addFlash('info', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart_index');
        }

        $user = $this->getUser();
        $prefill = [
            'customerName' => $user ? trim($user->getFirstname() . ' ' . $user->getLastname()) : '',
            'email' => $user ? $user->getUserIdentifier() : '',
        ];

        dd($prefill);

        $form = $this->createForm(CheckoutType::class, $prefill);
        $form->handleRequest($request);

        $couponCode = $cartService->getCouponCode();
        $coupon = null;
        if ($couponCode) {
            $coupon = $couponRepository->findActiveByCode($couponCode);
            if (!$coupon || !$coupon->isActive()) {
                $cartService->clearCoupon();
                $coupon = null;
            }
        }

        $previewSubtotal = $cartDetails['total'];
        $previewDiscount = 0;
        if ($coupon && $coupon->isActive()) {
            $now = new DateTimeImmutable();
            $isWithinDates = (!$coupon->getStartsAt() || $coupon->getStartsAt() <= $now)
                && (!$coupon->getEndsAt() || $coupon->getEndsAt() >= $now);
            $isWithinUsage = $coupon->getUsageLimit() === null || $coupon->getUsedCount() < $coupon->getUsageLimit();

            if ($isWithinDates && $isWithinUsage) {
                if ($coupon->getType() === 'percentage') {
                    $previewDiscount = (int) round($previewSubtotal * ($coupon->getValue() / 100));
                } else {
                    $previewDiscount = $coupon->getValue();
                }
                $previewDiscount = min($previewDiscount, $previewSubtotal);
            }
        }

        $selectedShipping = $form->get('shippingMethod')->getData() ?: 'standard';
        $previewShippingFee = $selectedShipping === 'express' ? 700 : 0;
        $previewTotal = max(0, $previewSubtotal - $previewDiscount) + $previewShippingFee;

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($cartDetails['items'] as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];
                $stock = $product->getStock();
                if ($stock !== null && $stock < $quantity) {
                    $this->addFlash('warning', 'Stock insuffisant pour ' . $product->getName() . '.');
                    return $this->redirectToRoute('app_cart_index');
                }
            }

            $data = $form->getData();
            $subtotal = $cartDetails['total'];
            $discount = $previewDiscount;

            $shippingMethod = $data['shippingMethod'];
            $shippingFee = $shippingMethod === 'express' ? 700 : 0;
            $paymentMethod = $data['paymentMethod'];

            $total = max(0, $subtotal - $discount) + $shippingFee;

            $order = new Order();
            $order->setUser($user);
            $order->setCustomerName($data['customerName']);
            $order->setEmail($data['email']);
            $order->setPhone($data['phone']);
            $order->setAddressLine1($data['addressLine1']);
            $order->setAddressLine2($data['addressLine2']);
            $order->setCity($data['city']);
            $order->setPostalCode($data['postalCode']);
            $order->setCountry($data['country']);
            $order->setStatus('pending');
            $order->setCouponCode($coupon ? $coupon->getCode() : null);
            $order->setSubtotal($subtotal);
            $order->setShippingMethod($shippingMethod);
            $order->setShippingFee($shippingFee);
            $order->setDiscountAmount($discount);
            $order->setPaymentMethod($paymentMethod);
            $order->setTotalAmount($total);
            $now = new DateTimeImmutable();
            $order->setCreatedAt($now);
            $order->setUpdatedAt($now);

            foreach ($cartDetails['items'] as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];
                $lineTotal = $item['lineTotal'];

                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setProductName($product->getName());
                $orderItem->setUnitPrice($product->getPrice());
                $orderItem->setQuantity($quantity);
                $orderItem->setLineTotal($lineTotal);
                $order->addItem($orderItem);

                $stock = $product->getStock();
                if ($stock !== null) {
                    $product->setStock(max(0, $stock - $quantity));
                }
            }

            $entityManager->persist($order);
            if ($coupon && $discount > 0) {
                $coupon->incrementUsage();
            }
            $entityManager->flush();

            $email = (new Email())
                ->from('no-reply@ecommerce.local')
                ->to($order->getEmail())
                ->subject('Confirmation de commande #' . $order->getId())
                ->html($this->renderView('emails/order_confirmation.html.twig', [
                    'order' => $order,
                ]));
            $mailer->send($email);

            $cartService->clear();
            $this->addFlash('success', 'Commande confirmee !');

            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
        }

        return $this->render('checkout/index.html.twig', [
            'form' => $form,
            'items' => $cartDetails['items'],
            'total' => $cartDetails['total'],
            'coupon' => $coupon,
            'preview_discount' => $previewDiscount,
            'preview_shipping_fee' => $previewShippingFee,
            'preview_total' => $previewTotal,
        ]);
    }
}
