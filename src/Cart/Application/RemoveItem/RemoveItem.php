<?php

namespace App\Cart\Application\RemoveItem;

use App\Cart\Domain\CartNotFoundException;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItemNotFoundException;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RemoveItem
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private CartItemRepositoryInterface $cartItemRepository,
        private ProductRepositoryInterface $productRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(string $productId, string $cartId): void
    {
        $product = $this->productRepository->search($productId);
        if (null === $product) {
            throw new ProductNotFoundException($productId);
        }
        $cart = $this->cartRepository->search($cartId);
        if (null === $cart) {
            throw new CartNotFoundException($cartId);
        }
        $cartItem = $this->cartItemRepository->findByProductAndCart($product, $cart);
        if (null === $cartItem) {
            throw new CartItemNotFoundException($productId, $cartId);
        }

        $this->entityManager->wrapInTransaction(function () use ($product, $cart, $cartItem) {
            $product->addStock($cartItem->getQuantity());

            $this->cartRepository->save($cart);
            $this->cartItemRepository->remove($cartItem);
        });
    }
}