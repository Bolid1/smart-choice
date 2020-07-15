<?php

declare(strict_types=1);

namespace App\Converter;

use App\Entity\Category;
use App\Entity\Company;

class CategoryConverter
{
    public function convert(string $identifier, array $context): ?Category
    {
        $company = $context['company'] ?? null;

        if ($company instanceof Company) {
            $category = $company->getCategoryById($identifier)
                ?: $company->getCategoryByName($identifier);
        }

        return $category ?? null;
    }
}
