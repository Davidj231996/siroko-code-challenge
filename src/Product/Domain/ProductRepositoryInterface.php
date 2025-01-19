<?php

namespace App\Product\Domain;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;

    public function search(string $id): ?Product;
}