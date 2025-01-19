<?php

namespace App\Cart\Domain;

use App\Order\Domain\Order;
use DateTimeImmutable;

class Cart
{
    private readonly string $id;
    private readonly DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    private ?Order $order = null;

    private function __construct()
    {
        $this->id = CartId::random();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public static function create(): self
    {
        return new self();
    }

    public function confirmCart(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }
}