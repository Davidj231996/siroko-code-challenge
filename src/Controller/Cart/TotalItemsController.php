<?php

namespace App\Controller\Cart;

use App\Cart\Application\TotalItems\TotalItems;
use App\Cart\Domain\CartNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

class TotalItemsController extends AbstractController
{
    public function __construct(private readonly TotalItems $totalItems) {}

    #[Route('/cart/totalItems', name: 'cart_total_items', methods: ['POST'])]
    public function getTotalCartProduct(Request $request): JsonResponse
    {
        $cartId = $request->get('cartId');
        if (null === $cartId) {
            return new JsonResponse(['error' => 'Cart id is required'], 400);
        }

        try {
            $total = $this->totalItems->__invoke($cartId);
        } catch (CartNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        } catch (Exception) {
            return new JsonResponse(['error' => 'An unexpected error occurred'], 500);
        }
        return new JsonResponse(['total' => $total], 200);
    }
}