<?php

namespace App\CartItem\Infrastructure\Persistence;

use App\Cart\Domain\Cart;
use App\CartItem\Domain\CartItem;
use App\CartItem\Domain\CartItemRepositoryInterface;
use App\Product\Domain\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartItem>
 */
class DoctrineCartItemRepository extends ServiceEntityRepository implements CartItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    public function save(CartItem $cartItem): void
    {
        $this->getEntityManager()->persist($cartItem);
    }

    public function remove(CartItem $cartItem): void
    {
        $this->getEntityManager()->remove($cartItem);
    }

    public function findByProductAndCart(Product $product, Cart $cart): ?CartItem
    {
        return $this->findOneBy(['product' => $product, 'cart' => $cart]);
    }

    public function findByCart(Cart $cart)
    {
        return $this->findBy(['cart' => $cart]);
    }
}