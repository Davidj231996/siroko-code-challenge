<?php

namespace App\OrderItem\Domain;

interface OrderItemRepositoryInterface
{
    public function save(OrderItem $orderItem);
}