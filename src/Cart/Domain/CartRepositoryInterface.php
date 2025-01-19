<?php

namespace App\Cart\Domain;

interface CartRepositoryInterface
{
    public function save(Cart $cart);

    public function search(string $id): ?Cart;
}