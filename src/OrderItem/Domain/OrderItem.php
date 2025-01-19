<?php

namespace App\OrderItem\Domain;

use App\Order\Domain\Order;
use App\Product\Domain\Product;
use DateTimeImmutable;

readonly class OrderItem
{
    private string $id;
    private DateTimeImmutable $createdAt;

    private function __construct(
        private Order $order,
        private Product $product,
        private int $quantity,
        private float $price
    ) {
        $this->id = OrderItemId::random();
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        Order $order,
        Product $product,
        int $quantity,
        float $price
    ): self
    {
        return new self($order, $product, $quantity, $price);
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}