<?php

declare(strict_types=1);

namespace App\Helper;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use UnexpectedValueException;

final class DateTimeHelper
{
    public static function toImmutable(DateTimeInterface $dateTime): DateTimeImmutable
    {
        if ($dateTime instanceof DateTime) {
            $result = DateTimeImmutable::createFromMutable($dateTime);
        } elseif ($dateTime instanceof DateTimeImmutable) {
            $result = $dateTime;
        } else {
            throw new UnexpectedValueException('Unexpected implementation of DateTimeInterface: ', \get_class($dateTime));
        }

        return $result;
    }
}
