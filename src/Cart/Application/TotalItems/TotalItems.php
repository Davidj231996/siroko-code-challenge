<?php

namespace App\Cart\Application\TotalItems;

use App\Cart\Domain\CartNotFoundException;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItemRepositoryInterface;

final readonly class TotalItems
{
    public function __construct(
        private CartItemRepositoryInterface $cartItemRepository,
        private CartRepositoryInterface $cartRepository
    ) {}

    public function __invoke(string $cartId): int
    {
        $cart = $this->cartRepository->search($cartId);
        if (null === $cart) {
            throw new CartNotFoundException($cartId);
        }
        $cartItems = $this->cartItemRepository->findByCart($cart);
        return array_sum(array_map(fn($item) => $item->getQuantity(), $cartItems));
    }
}