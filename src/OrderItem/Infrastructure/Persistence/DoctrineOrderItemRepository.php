<?php

namespace App\OrderItem\Infrastructure\Persistence;

use App\OrderItem\Domain\OrderItem;
use App\OrderItem\Domain\OrderItemRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 */
class DoctrineOrderItemRepository extends ServiceEntityRepository implements OrderItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    public function save(OrderItem $orderItem): void
    {
        $this->getEntityManager()->persist($orderItem);
    }
}