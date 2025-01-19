<?php

namespace App\Tests\Unit;

use App\Cart\Application\AddItem\AddItem;
use App\Cart\Domain\Cart;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItem;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Product\Domain\Product;
use App\Product\Domain\ProductNotFoundException;
use App\Product\Domain\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddItemTest extends TestCase
{
    private AddItem $addItem;
    private MockObject $cartRepositoryMock;
    private MockObject $productRepositoryMock;
    private MockObject $cartItemRepositoryMock;
    private MockObject $entityManagerMock;

    protected function setUp(): void
    {
        // Crear mocks para las interfaces
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->cartItemRepositoryMock = $this->createMock(CartItemRepositoryInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        // Crear el servicio AddItem
        $this->addItem = new AddItem(
            $this->cartRepositoryMock,
            $this->productRepositoryMock,
            $this->cartItemRepositoryMock,
            $this->entityManagerMock
        );
    }

    public function testAddItem(): void
    {
        $productId = 'product1';
        $quantity = 1;
        $cartId = 'cart1';
        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn($productId);

        $cartMock = $this->createMock(Cart::class);
        $cartMock->method('getId')->willReturn($cartId);

        $cartItemMock = $this->createMock(CartItem::class);
        $cartItemMock->method('getQuantity')->willReturn($quantity);

        $this->productRepositoryMock->expects($this->once())
            ->method('search')
            ->with($productId)
            ->willReturn($productMock);


        $this->entityManagerMock
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) use ($cartId) {
                // Llamamos al callback que simula la lógica de negocio
                $callback();
                return $cartId;
            });

        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Cart::class));
        $this->cartItemRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(CartItem::class));
        $this->productRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Product::class));

        $generatedCartId = ($this->addItem)($productId, $quantity);

        $this->assertEquals($generatedCartId, $cartId);
    }

    public function testAddProductToExistingCart(): void
    {
        // Datos de entrada
        $productId = 'product-123';
        $quantity = 3;
        $cartId = 'cart-123';

        // Crear un producto mock
        $productMock = $this->createMock(Product::class);
        $productMock->method('getId')->willReturn($productId);

        // Simular que el producto existe en el repositorio
        $this->productRepositoryMock->method('search')->willReturn($productMock);

        // Crear un carrito mock
        $cartMock = $this->createMock(Cart::class);
        $cartMock->method('getId')->willReturn($cartId);
        $this->cartRepositoryMock->method('search')->willReturn($cartMock);

        // Crear un CartItem mock
        $cartItemMock = $this->createMock(CartItem::class);

        // Simular que se crea un CartItem al añadir el producto al carrito
        $this->cartItemRepositoryMock->expects($this->once())->method('save')->with($this->isInstanceOf(CartItem::class));

        // Simular la llamada a wrapInTransaction
        $this->entityManagerMock
            ->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) use ($cartMock) {
                return $callback();
            });

        // Esperamos que se guarden las entidades correspondientes
        $this->cartRepositoryMock->expects($this->once())->method('save')->with($this->isInstanceOf(Cart::class));
        $this->productRepositoryMock->expects($this->once())->method('save')->with($this->isInstanceOf(Product::class));

        // Invocar el servicio AddItem
        $cartIdReturned = ($this->addItem)($productId, $quantity, $cartId);

        // Verificar que el ID del carrito retornado es el correcto
        $this->assertEquals($cartId, $cartIdReturned);
    }

    public function testProductNotFoundException(): void
    {
        // Datos de entrada
        $productId = 'non-existent-product';
        $quantity = 2;

        // Simular que no se encuentra el producto en el repositorio
        $this->productRepositoryMock->method('search')->willReturn(null);

        // Llamar al servicio y esperar la excepción ProductNotFoundException
        $this->expectException(ProductNotFoundException::class);

        // Invocar el servicio AddItem
        ($this->addItem)($productId, $quantity);
    }
}