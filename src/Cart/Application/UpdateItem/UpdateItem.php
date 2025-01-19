<?php

namespace App\Cart\Application\UpdateItem;

use App\Cart\Domain\CartNotFoundException;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UpdateItem
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CartItemRepositoryInterface $cartItemRepository,
        private CartRepositoryInterface $cartRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(string $productId, int $quantity, string $cartId = null): void
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
            throw new CartNotFoundException($cartId);
        }

        $this->entityManager->wrapInTransaction(function () use ($cartItem, $product, $quantity) {
            $quantity > $cartItem->getQuantity() ? $product->reduceStock($quantity) : $product->addStock($quantity);
            $cartItem->updateQuantity($quantity);

            $this->cartItemRepository->save($cartItem);
            $this->productRepository->save($product);
        });
    }
}