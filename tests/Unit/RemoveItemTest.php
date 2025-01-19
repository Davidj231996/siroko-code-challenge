<?php

namespace App\Tests\Unit;

use App\Cart\Application\RemoveItem\RemoveItem;
use App\Cart\Domain\Cart;
use App\Cart\Domain\CartNotFoundException;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItem;
use App\CartItem\Domain\CartItemNotFoundException;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Product\Domain\Product;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RemoveItemTest extends TestCase
{
    private RemoveItem $removeItem;
    private MockObject $cartRepositoryMock;
    private MockObject $cartItemRepositoryMock;
    private MockObject $productRepositoryMock;
    private MockObject $entityManagerMock;

    protected function setUp(): void
    {
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->cartItemRepositoryMock = $this->createMock(CartItemRepositoryInterface::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->removeItem = new RemoveItem(
            $this->cartRepositoryMock,
            $this->cartItemRepositoryMock,
            $this->productRepositoryMock,
            $this->entityManagerMock
        );
    }

    public function testRemoveItem(): void
    {
        $productId = 'product-123';
        $cartId = 'cart-123';
        $quantity = 2;

        $productMock = $this->createMock(Product::class);
        $cartMock = $this->createMock(Cart::class);
        $cartItemMock = $this->createMock(CartItem::class);

        $this->productRepositoryMock->expects($this->once())
            ->method('search')
            ->with($productId)
            ->willReturn($productMock);

        $this->cartRepositoryMock->expects($this->once())
            ->method('search')
            ->with($cartId)
            ->willReturn($cartMock);

        $this->cartItemRepositoryMock->expects($this->once())
            ->method('findByProductAndCart')
            ->with($productMock, $cartMock)
            ->willReturn($cartItemMock);

        $cartItemMock->expects($this->once())
            ->method('getQuantity')
            ->willReturn($quantity);

        $productMock->expects($this->once())
            ->method('addStock')
            ->with($quantity);

        $this->entityManagerMock->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) {
                $callback();
            });

        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($cartMock);

        $this->cartItemRepositoryMock->expects($this->once())
            ->method('remove')
            ->with($cartItemMock);

        $this->removeItem->__invoke($productId, $cartId);
    }

    public function testProductNotFoundException(): void
    {
        $productId = 'non-existent-product';
        $cartId = 'cart-123';

        $this->productRepositoryMock->expects($this->once())
            ->method('search')
            ->with($productId)
            ->willReturn(null);

        $this->expectException(ProductNotFoundException::class);

        $this->removeItem->__invoke($productId, $cartId);
    }

    public function testCartNotFoundException(): void
    {
        $productId = 'product-123';
        $cartId = 'non-existent-cart';

        $productMock = $this->createMock(Product::class);

        $this->productRepositoryMock->expects($this->once())
            ->method('search')
            ->with($productId)
            ->willReturn($productMock);

        $this->cartRepositoryMock->expects($this->once())
            ->method('search')
            ->with($cartId)
            ->willReturn(null);

        $this->expectException(CartNotFoundException::class);

        $this->removeItem->__invoke($productId, $cartId);
    }

    public function testCartItemNotFoundException(): void
    {
        $productId = 'product-123';
        $cartId = 'cart-123';

        $productMock = $this->createMock(Product::class);
        $cartMock = $this->createMock(Cart::class);

        $this->productRepositoryMock->expects($this->once())
            ->method('search')
            ->with($productId)
            ->willReturn($productMock);

        $this->cartRepositoryMock->expects($this->once())
            ->method('search')
            ->with($cartId)
            ->willReturn($cartMock);

        $this->cartItemRepositoryMock->expects($this->once())
            ->method('findByProductAndCart')
            ->with($productMock, $cartMock)
            ->willReturn(null);

        $this->expectException(CartItemNotFoundException::class);

        $this->removeItem->__invoke($productId, $cartId);
    }
}