<?php

namespace App\Controller\Cart;

use App\Cart\Application\RemoveItem\RemoveItem;
use App\Cart\Domain\CartNotFoundException;
use App\CartItem\Domain\CartItemNotFoundException;
use App\Product\Domain\ProductNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

class RemoveItemController extends AbstractController
{
    public function __construct(private readonly RemoveItem $removeItem) {}

    #[Route('/cart/remove', name: 'cart_remove_item', methods: ['POST'])]
    public function removeItem(Request $request): JsonResponse
    {
        $productId = $request->get('productId');
        if (null === $productId) {
            return new JsonResponse(['error' => 'Product id is required'], 400);
        }
        $cartId = $request->get('cartId');
        if (null === $cartId) {
            return new JsonResponse(['error' => 'Cart id is required'], 400);
        }

        try {
            $this->removeItem->__invoke($productId, $cartId);
        } catch (CartNotFoundException|ProductNotFoundException|CartItemNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        } catch (Exception) {
            return new JsonResponse(['error' => 'An unexpected error occurred'], 500);
        }

        return new JsonResponse(['success' => 'Item removed from cart'], 200);
    }
}