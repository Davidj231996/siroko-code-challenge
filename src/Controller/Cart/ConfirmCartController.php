<?php

namespace App\Controller\Cart;

use App\Cart\Application\ConfirmCart\ConfirmCart;
use App\Cart\Domain\CartNotFoundException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConfirmCartController extends AbstractController
{
    public function __construct(private readonly ConfirmCart $confirmCart) {}

    #[Route('/cart/confirm', name: 'cart_confirm', methods: ['POST'])]
    public function confirmCart(Request $request): JsonResponse
    {
        $cartId = $request->get('cartId');
        if (null === $cartId) {
            return new JsonResponse(['error' => 'Cart id is required'], 400);
        }

        try {
            $orderId = $this->confirmCart->__invoke($cartId);
        } catch (CartNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        } catch (Exception) {
            return new JsonResponse(['error' => 'An unexpected error occurred'], 500);
        }
        return new JsonResponse(['success' => 'Order created', 'orderId' => $orderId], 200);
    }
}