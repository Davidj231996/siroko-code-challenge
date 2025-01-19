<?php

namespace App\Controller\Cart;

use App\Cart\Application\UpdateItem\UpdateItem;
use App\Cart\Domain\CartNotFoundException;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductStockNotEnoughException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

class UpdateItemController extends AbstractController
{
    public function __construct(private readonly UpdateItem $updateItem) {}

    #[Route('/cart/update', name: 'cart_update_item', methods: ['POST'])]
    public function updateItem(Request $request): JsonResponse
    {
        $productId = $request->get('productId');
        if (null === $productId) {
            return new JsonResponse(['error' => 'Product id is required'], 400);
        }
        $quantity = $request->get('quantity');
        if (null === $quantity) {
            return new JsonResponse(['error' => 'Quantity is required'], 400);
        }
        $cartId = $request->get('cartId');
        if (null === $cartId) {
            return new JsonResponse(['error' => 'Cart id is required'], 400);
        }

        try {
            $this->updateItem->__invoke($productId, $quantity, $cartId);
        } catch (CartNotFoundException|ProductNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        } catch (ProductStockNotEnoughException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 409);
        } catch (Exception) {
            return new JsonResponse(['error' => 'An unexpected error occurred'], 500);
        }

        return new JsonResponse(['success' => 'Item updated in cart'], 200);
    }
}