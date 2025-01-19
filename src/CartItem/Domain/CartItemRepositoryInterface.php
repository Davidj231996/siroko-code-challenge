<?php

namespace App\CartItem\Domain;

use App\Cart\Domain\Cart;
use App\Product\Domain\Product;

interface CartItemRepositoryInterface
{
    public function save(CartItem $cartItem): void;

    public function remove(CartItem $cartItem): void;

    public function findByProductAndCart(Product $product, Cart $cart): ?CartItem;

    public function findByCart(Cart $cart);
}