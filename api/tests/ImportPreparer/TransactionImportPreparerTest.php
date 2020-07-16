<?php

declare(strict_types=1);

namespace App\Tests\ImportPreparer;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Converter\AccountConverter;
use App\Converter\CategoryConverter;
use App\Entity\Account;
use App\Entity\Category;
use App\ImportPreparer\TransactionImportPreparer;
use PHPUnit\Framework\TestCase;

class TransactionImportPreparerTest extends TestCase
{
    /**
     * @covers \App\ImportPreparer\TransactionImportPreparer::__construct
     * @covers \App\ImportPreparer\TransactionImportPreparer::prepare
     */
    public function testPrepare(): void
    {
        $preparer = new TransactionImportPreparer(
            $accountConverter = $this->createMock(AccountConverter::class),
            $categoryConverter = $this->createMock(CategoryConverter::class),
            $converter = $this->createMock(IriConverterInterface::class),
        );

        $data = [
            'account' => 'Some account',
            'amount' => '123,65',
            'date' => '',
            'categories' => 'Sport, Home',
        ];
        $context = [
            'foo' => 'bar',
        ];

        $accountConverter
            ->expects(self::once())
            ->method('convert')
            ->with($data['account'], $context)
            ->willReturn($account = new Account())
        ;

        $categoryConverter
            ->expects(self::exactly(2))
            ->method('convert')
            ->withConsecutive(['Sport', $context], ['Home', $context])
            ->willReturn($category1 = new Category(), $category2 = new Category())
        ;

        $converter
            ->expects(self::once())
            ->method('getIriFromItem')
            ->with($account)
            ->willReturn($accountIri = '/foo/bar/baz')
        ;

        $expected = [
            'account' => $accountIri,
            'amount' => 123.65,
            'categories' => [$category1, $category2],
        ];

        self::assertEquals($expected, $preparer->prepare($data, $context));
    }
}
