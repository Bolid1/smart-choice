<?php

declare(strict_types=1);

namespace App\ImportPreparer;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Converter\AccountConverter;
use App\Converter\CategoryConverter;
use App\Entity\Company;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class TransactionImportPreparer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private AccountConverter $accountConverter;
    private CategoryConverter $categoryConverter;
    private IriConverterInterface $converter;

    /**
     * TransactionImportPreparer constructor.
     *
     * @param \App\Converter\AccountConverter $accountConverter
     * @param \App\Converter\CategoryConverter $categoryConverter
     * @param \ApiPlatform\Core\Api\IriConverterInterface $converter
     */
    public function __construct(
        AccountConverter $accountConverter,
        CategoryConverter $categoryConverter,
        IriConverterInterface $converter
    ) {
        $this->accountConverter = $accountConverter;
        $this->categoryConverter = $categoryConverter;
        $this->converter = $converter;
        $this->logger = new NullLogger();
    }

    public function prepare(array $data, array $context): array
    {
        $this->logger->info('Prepare new transaction data', \compact('data', 'context'));

        if (isset($data['account']) && \is_string($data['account'])) {
            $identifier = \trim($data['account']);
            $account = $this->accountConverter->convert($identifier, $context);
            if (null !== $account) {
                $data['account'] = $this->converter->getIriFromItem($account);
                $this->logger->debug("Found account '{$data['account']}' by identifier '{$identifier}'");
            } else {
                $this->logger->notice("Account not found by identifier '{$identifier}'");
            }
        }

        if (isset($data['amount']) && \is_string($data['amount'])) {
            $amount = \str_replace([','], ['.'], $data['amount']);
            if (\is_numeric($amount)) {
                $data['amount'] = (float)$amount;
            }
        }

        if (empty($data['date'])) {
            unset($data['date']);
        }

        if (isset($data['categories'])) {
            $categories = [];
            foreach (\explode(',', $data['categories']) as $identifier) {
                $category = $this->categoryConverter->convert(\trim($identifier), $context);
                if (null !== $category) {
                    $categories[] = $category;
                    $this->logger->debug("Found category '{$category}' by identifier '{$identifier}'");
                } else {
                    $this->logger->notice("Category not found by identifier '{$identifier}'");
                }
            }

            $data['categories'] = $categories;
        }

        if (isset($context['company']) && $context['company'] instanceof Company) {
            $data['company'] = $this->converter->getIriFromItem($context['company']);
        }

        $this->logger->info('Prepared data', \compact('data'));

        return $data;
    }
}
