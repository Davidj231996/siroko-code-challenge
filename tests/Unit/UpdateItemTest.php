<?php

namespace App\Tests\Unit;

use App\Cart\Application\UpdateItem\UpdateItem;
use App\Cart\Domain\Cart;
use App\Cart\Domain\CartNotFoundException;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItem;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Product\Domain\Product;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateItemTest extends TestCase
{
    private UpdateItem $updateItem;
    private MockObject $productRepositoryMock;
    private MockObject $cartItemRepositoryMock;
    private MockObject $cartRepositoryMock;
    private MockObject $entityManagerMock;

    protected function setUp(): void
    {
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->cartItemRepositoryMock = $this->createMock(CartItemRepositoryInterface::class);
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->updateItem = new UpdateItem(
            $this->productRepositoryMock,
            $this->cartItemRepositoryMock,
            $this->cartRepositoryMock,
            $this->entityManagerMock
        );
    }

    public function testUpdateItemIncreasesQuantity(): void
    {
        $productId = 'product-123';
        $cartId = 'cart-123';
        $quantity = 5;

        $productMock = $this->createMock(Product::class);
        $cartMock = $this->createMock(Cart::class);
        $cartItemMock = $this->createMock(CartItem::class);

        $cartItemMock->expects($this->once())
            ->method('getQuantity')
            ->willReturn(3);
        $cartItemMock->expects($this->once())
            ->method('updateQuantity')
            ->with($quantity);

        $productMock->expects($this->once())
            ->method('reduceStock')
            ->with($quantity);

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

        $this->entityManagerMock
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) {
                $callback();
            });

        ($this->updateItem)($productId, $quantity, $cartId);
    }

    public function testUpdateItemDecreasesQuantity(): void
    {
        $productId = 'product-123';
        $cartId = 'cart-123';
        $quantity = 2;

        $productMock = $this->createMock(Product::class);
        $cartMock = $this->createMock(Cart::class);
        $cartItemMock = $this->createMock(CartItem::class);

        $cartItemMock->expects($this->once())
            ->method('getQuantity')
            ->willReturn(5);
        $cartItemMock->expects($this->once())
            ->method('updateQuantity')
            ->with($quantity);

        $productMock->expects($this->once())
            ->method('addStock')
            ->with($quantity);

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

        $this->entityManagerMock
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) {
                $callback();
            });

        ($this->updateItem)($productId, $quantity, $cartId);
    }

    public function testProductNotFoundException(): void
    {
        $productId = 'non-existent-product';
        $cartId = 'cart-123';
        $quantity = 1;

        $this->productRepositoryMock->expects($this->once())
            ->method('search')
            ->with($productId)
            ->willReturn(null);

        $this->expectException(ProductNotFoundException::class);

        ($this->updateItem)($productId, $quantity, $cartId);
    }

    public function testCartNotFoundException(): void
    {
        $productId = 'product-123';
        $cartId = 'non-existent-cart';
        $quantity = 1;

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

        ($this->updateItem)($productId, $quantity, $cartId);
    }
}