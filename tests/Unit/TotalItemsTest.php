<?php

namespace App\Tests\Unit;

use App\Cart\Application\TotalItems\TotalItems;
use App\Cart\Domain\Cart;
use App\Cart\Domain\CartNotFoundException;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItem;
use App\CartItem\Domain\CartItemRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TotalItemsTest extends TestCase
{
    private TotalItems $totalItems;
    private MockObject $cartItemRepositoryMock;
    private MockObject $cartRepositoryMock;

    protected function setUp(): void
    {
        $this->cartItemRepositoryMock = $this->createMock(CartItemRepositoryInterface::class);
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);

        $this->totalItems = new TotalItems(
            $this->cartItemRepositoryMock,
            $this->cartRepositoryMock
        );
    }

    public function testTotalItems(): void
    {
        $cartId = 'cart-123';
        $cartMock = $this->createMock(Cart::class);

        $cartItem1Mock = $this->createMock(CartItem::class);
        $cartItem1Mock->expects($this->once())->method('getQuantity')->willReturn(2);

        $cartItem2Mock = $this->createMock(CartItem::class);
        $cartItem2Mock->expects($this->once())->method('getQuantity')->willReturn(3);

        $this->cartRepositoryMock->expects($this->once())
            ->method('search')
            ->with($cartId)
            ->willReturn($cartMock);

        $this->cartItemRepositoryMock->expects($this->once())
            ->method('findByCart')
            ->with($cartMock)
            ->willReturn([$cartItem1Mock, $cartItem2Mock]);

        $total = ($this->totalItems)($cartId);

        $this->assertEquals(5, $total);
    }

    public function testCartNotFoundException(): void
    {
        $cartId = 'non-existent-cart';

        $this->cartRepositoryMock->expects($this->once())
            ->method('search')
            ->with($cartId)
            ->willReturn(null);

        $this->expectException(CartNotFoundException::class);

        ($this->totalItems)($cartId);
    }

    public function testTotalItemsEmptyCart(): void
    {
        $cartId = 'empty-cart';
        $cartMock = $this->createMock(Cart::class);

        $this->cartRepositoryMock->expects($this->once())
            ->method('search')
            ->with($cartId)
            ->willReturn($cartMock);

        $this->cartItemRepositoryMock->expects($this->once())
            ->method('findByCart')
            ->with($cartMock)
            ->willReturn([]);

        $total = ($this->totalItems)($cartId);

        $this->assertEquals(0, $total);
    }
}