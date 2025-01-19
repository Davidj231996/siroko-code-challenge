<?php

namespace App\Controller;

use App\Cart\Application\AddItem\AddItem;
use App\Cart\Application\ConfirmCart\ConfirmCart;
use App\Cart\Application\RemoveItem\RemoveItem;
use App\Cart\Application\TotalItems\TotalItems;
use App\Cart\Application\UpdateItem\UpdateItem;
use App\Cart\Domain\CartNotFoundException;
use App\CartItem\Domain\CartItemNotFoundException;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductStockNotEnoughException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    public function __construct(
      private readonly AddItem $addItem,
      private readonly UpdateItem $updateItem,
      private readonly RemoveItem $removeItem,
      private readonly TotalItems $totalItems,
      private readonly ConfirmCart $confirmCart
    ) {}

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