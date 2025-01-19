<?php

namespace App\Controller\Cart;

use App\Cart\Application\AddItem\AddItem;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductStockNotEnoughException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Exception;

class AddItemController extends AbstractController
{
    public function __construct(private readonly AddItem $addItem) {}

    #[Route('/cart/add', name: 'cart_add_item', methods: ['POST'])]
    public function addItem(Request $request): JsonResponse
    {
        $productId = $request->get('productId');
        if (null === $productId) {
            return new JsonResponse(['error' => 'Product id is required'], 400);
        }
        $quantity = (int) $request->get('quantity', 1);
        $cartId = $request->get('cartId');

        try {
            $cartId = $this->addItem->__invoke($productId, $quantity, $cartId);
        } catch (ProductNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 404);
        } catch (ProductStockNotEnoughException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 409);
        } catch (Exception) {
            return new JsonResponse(['error' => 'An unexpected error occurred'], 500);
        }
        return new JsonResponse(['success' => 'Item added to cart', 'cartId' => $cartId], 200);
    }
}