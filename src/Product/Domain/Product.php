<?php

namespace App\Product\Domain;

use DateTimeImmutable;

class Product
{
    private readonly string $id;
    private readonly DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        private string $name,
        private float $price,
        private int $stock
    ) {
        $this->id = ProductId::random();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public static function create(
        string $name,
        float $price,
        int $stock
    ): self {
        return new self($name, $price, $stock);
    }

    // Function to reduce stock of product
    public function reduceStock(int $quantity): void
    {
        if ($this->stock < $quantity) {
            throw new ProductStockNotEnoughException($this->id);
        }
        $this->stock -= $quantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Function to reduce stock of product
    public function addStock(int $quantity): void
    {
        $this->stock += $quantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStock(): int
    {
        return $this->stock;
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