<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateTimeExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    /**
     * DateTimeExtension constructor.
     *
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('date_ago', [$this, 'dateAgo']),
        ];
    }

    public function dateAgo(\DateTimeInterface $date): ?string
    {
        $compare = new \DateTimeImmutable();
        $interval = $date->diff($compare);
        $ago = [];

        if ($daysAgo = $count = $interval->format('%a')) {
            $ago[] = $this->translator->trans('day|%count% days', ['%count%' => $count]);
        }

        if ($hoursAgo = $count = $interval->format('%h')) {
            $ago[] = $this->translator->trans('hour|%count% hours', ['%count%' => $count]);
        }

        if (!$daysAgo && $count = $interval->format('%i')) {
            $ago[] = $this->translator->trans('minute|%count% minutes', ['%count%' => $count]);
        }

        if (!$daysAgo && !$hoursAgo && $count = $interval->format('%s')) {
            $ago[] = $this->translator->trans('second|%count% seconds', ['%count%' => $count]);
        }

        return \implode(' ', $ago);
    }
}
