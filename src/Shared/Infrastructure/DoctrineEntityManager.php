<?php

namespace App\Shared\Infrastructure;

use Doctrine\ORM\EntityManagerInterface;

abstract class DoctrineEntityManager implements EntityManagerInterface
{
    public function wrapInTransaction(callable $func): mixed
    {
        $this->beginTransaction();

        try {
            $func();
            $this->flush();
            $this->commit();
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}