<?php

namespace App\CartItem\Domain;

use App\Cart\Domain\Cart;
use App\Product\Domain\Product;
use DateTimeImmutable;

class CartItem
{
    private readonly string $id;
    private readonly DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        private readonly Product $product,
        private readonly Cart $cart,
        private int $quantity
    ) {
        $this->id = CartItemId::random();

        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public static function create(Product $product, Cart $cart, int $quantity): self
    {
        $product->reduceStock($quantity);
        return new self($product, $cart, $quantity);
    }

    public function updateQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    // Getter
    public function getId(): string
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}