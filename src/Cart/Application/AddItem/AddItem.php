<?php

namespace App\Cart\Application\AddItem;

use App\Cart\Domain\Cart;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItem;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AddItem
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private ProductRepositoryInterface $productRepository,
        private CartItemRepositoryInterface $cartItemRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(string $productId, int $quantity, ?string $cartId = null): string
    {
        $product = $this->productRepository->search($productId);
        if (null === $product) {
            throw new ProductNotFoundException($productId);
        }

        return $this->entityManager->wrapInTransaction(function () use ($product, $quantity, $cartId) {
            if (null === $cartId) {
                $cart = Cart::create();
            } else {
                $cart = $this->cartRepository->search($cartId);
            }
            $cartItem = CartItem::create($product, $cart, $quantity);

            $this->cartRepository->save($cart);
            $this->productRepository->save($product);
            $this->cartItemRepository->save($cartItem);

            return $cart->getId();
        });
    }
}