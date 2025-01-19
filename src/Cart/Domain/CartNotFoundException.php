<?php

namespace App\Cart\Domain;

use DomainException;

class CartNotFoundException extends DomainException
{
    public function __construct(private readonly string $id)
    {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'cart_not_found';
    }

    protected function errorMessage(): string
    {
        return sprintf('The cart <%s> has not been found', $this->id);
    }
}