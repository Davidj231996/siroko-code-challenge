<?php

namespace App\Product\Domain;

use DomainException;

class ProductNotFoundException extends DomainException
{
    public function __construct(private readonly string $id)
    {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'product_not_found';
    }

    protected function errorMessage(): string
    {
        return sprintf('The product <%s> has not been found', $this->id);
    }
}