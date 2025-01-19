<?php

namespace App\CartItem\Domain;

use DomainException;

class CartItemNotFoundException extends DomainException
{
    public function __construct(
        private readonly string $productId,
        private readonly string $cartId
    ) {
        parent::__construct();
    }

    public function errorCode(): string
    {
        return 'cart_item_not_found';
    }

    protected function errorMessage(): string
    {
        return sprintf('The product <%s> has not been found in cart <%s>', $this->productId, $this->cartId);
    }
}