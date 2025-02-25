<?php

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid as RamseyUuid;

abstract class Uuid
{
    final public function __construct(protected string $uuid)
    {
        $this->isValid($uuid);
    }

    final public static function random(): self
    {
        return new static(RamseyUuid::uuid4()->toString());
    }

    final public function uuid(): string
    {
        return $this->uuid;
    }

    final public function equals(self $other): bool
    {
        return $this->uuid() === $other->uuid();
    }

    public function __toString(): string
    {
        return $this->uuid();
    }

    private function isValid(string $uuid): void
    {
        if (!RamseyUuid::isValid($uuid)) {
            throw new InvalidArgumentException(sprintf('<%s> does not allow the value <%s>.', self::class, $uuid));
        }
    }
}