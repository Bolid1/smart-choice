<?php

declare(strict_types=1);

namespace App\Converter;

use App\Entity\Account;
use App\Entity\Company;

class AccountConverter
{
    public function convert(string $identifier, array $context): ?Account
    {
        $company = $context['company'] ?? null;

        if ($company instanceof Company) {
            $account = $company->getAccountById($identifier)
                ?: $company->getAccountByName($identifier);
        }

        return $account ?? null;
    }
}
