<?php

namespace App\Order\Domain;

use App\Cart\Domain\Cart;
use DateTimeImmutable;

class Order
{
    private readonly string $id;
    private readonly DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    private string $status = OrderStatus::PENDING->value;

    private function __construct(
        private readonly Cart $cart
    )
    {
        $this->id = OrderId::random();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public static function create(Cart $cart): Order
    {
        return new self($cart);
    }

    public function updateStatus(OrderStatus $status): void
    {
        $this->status = $status->value;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function cart(): Cart
    {
        return $this->cart;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}