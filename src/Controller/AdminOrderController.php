<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/orders')]
#[IsGranted('ROLE_ADMIN')]
final class AdminOrderController extends AbstractController
{
    private const STATUSES = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];

    #[Route('', name: 'app_admin_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'statuses' => self::STATUSES,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
            'statuses' => self::STATUSES,
        ]);
    }

    #[Route('/{id}/status', name: 'app_admin_order_status', methods: ['POST'])]
    public function updateStatus(
        Request $request,
        Order $order,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('order_status_' . $order->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
        }

        $status = (string) $request->request->get('status', '');
        if (!in_array($status, self::STATUSES, true)) {
            $this->addFlash('warning', 'Statut invalide.');
            return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
        }

        $order->setStatus($status);
        $order->setUpdatedAt(new DateTimeImmutable());
        $entityManager->flush();

        $this->addFlash('success', 'Statut mis a jour.');

        return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
    }
}
