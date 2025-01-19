<?php

namespace App\Tests\Unit;

use App\Cart\Application\ConfirmCart\ConfirmCart;
use App\Cart\Domain\Cart;
use App\Cart\Domain\CartNotFoundException;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItem;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Order\Domain\Order;
use App\Order\Domain\OrderRepositoryInterface;
use App\OrderItem\Domain\OrderItem;
use App\OrderItem\Domain\OrderItemRepositoryInterface;
use App\Product\Domain\Product;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConfirmCartTest extends TestCase
{
    private ConfirmCart $confirmCart;
    private MockObject $cartRepositoryMock;
    private MockObject $cartItemRepositoryMock;
    private MockObject $orderRepositoryMock;
    private MockObject $orderItemRepositoryMock;
    private MockObject $entityManagerMock;

    protected function setUp(): void
    {
        // Crear mocks para las interfaces
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->orderItemRepositoryMock = $this->createMock(OrderItemRepositoryInterface::class);
        $this->cartItemRepositoryMock = $this->createMock(CartItemRepositoryInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        // Crear el servicio AddItem
        $this->confirmCart = new ConfirmCart(
            $this->cartRepositoryMock,
            $this->cartItemRepositoryMock,
            $this->orderRepositoryMock,
            $this->orderItemRepositoryMock,
            $this->entityManagerMock
        );
    }

    public function testConfirmCart(): void
    {
        $cartId = 'cart-123';
        $orderId = 'order-123';

        $cartMock = $this->createMock(Cart::class);
        $cartMock->method('getId')->willReturn($cartId);

        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getId')->willReturn($orderId);

        $cartItemMock = $this->createMock(CartItem::class);
        $cartItemMock->method('getProduct')->willReturn($this->createMock(Product::class));
        $cartItemMock->method('getQuantity')->willReturn(2);

        $this->cartRepositoryMock->expects($this->once())
            ->method('search')
            ->with($cartId)
            ->willReturn($cartMock);

        $this->cartItemRepositoryMock->expects($this->once())
            ->method('findByCart')
            ->with($cartMock)
            ->willReturn([$cartItemMock]);

        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Order::class));

        $this->orderItemRepositoryMock->expects($this->exactly(1))
            ->method('save')
            ->with($this->isInstanceOf(OrderItem::class));

        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($cartMock);

        $this->entityManagerMock->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) use ($orderId) {
                $callback();
                return $orderId;
            });

        $resultOrderId = ($this->confirmCart)($cartId);

        $this->assertEquals($orderId, $resultOrderId);
    }

    public function testCartNotFoundException(): void
    {
        $cartId = 'non-existent-cart';

        $this->cartRepositoryMock->expects($this->once())
            ->method('search')
            ->with($cartId)
            ->willReturn(null);

        $this->expectException(CartNotFoundException::class);

        ($this->confirmCart)($cartId);
    }
}