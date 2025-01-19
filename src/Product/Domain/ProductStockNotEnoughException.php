<?php

namespace App\Product\Domain;

use DomainException;

class ProductStockNotEnoughException extends DomainException
{
    public function __construct(private readonly string $id)
    {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'product_stock_not_enough';
    }

    protected function errorMessage(): string
    {
        return sprintf('The product <%s> has not enough stock', $this->id);
    }
}