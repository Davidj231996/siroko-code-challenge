<?php

namespace App\Cart\Application\ConfirmCart;

use App\Cart\Domain\CartNotFoundException;
use App\Cart\Domain\CartRepositoryInterface;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Order\Domain\Order;
use App\Order\Domain\OrderRepositoryInterface;
use App\OrderItem\Domain\OrderItem;
use App\OrderItem\Domain\OrderItemRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ConfirmCart
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private CartItemRepositoryInterface $cartItemRepository,
        private OrderRepositoryInterface $orderRepository,
        private OrderItemRepositoryInterface $orderItemRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function __invoke(string $cartId): string
    {
        $cart = $this->cartRepository->search($cartId);
        if (null === $cart) {
            throw new CartNotFoundException($cartId);
        }

        return $this->entityManager->wrapInTransaction(function () use ($cart) {
            $cartItems = $this->cartItemRepository->findByCart($cart);
            $order = Order::create($cart);

            $cart->confirmCart();

            $this->orderRepository->save($order);

            foreach ($cartItems as $item) {
                $orderItem = OrderItem::create(
                    order: $order,
                    product: $item->getProduct(),
                    quantity: $item->getQuantity(),
                    price: $item->getProduct()->getPrice()
                );

                $this->orderItemRepository->save($orderItem);
            }
            $this->cartRepository->save($cart);

            return $order->getId();
        });
    }
}