<?php

declare(strict_types=1);

namespace App\Tests\ImportPreparer;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Converter\AccountConverter;
use App\Entity\Account;
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
            $converter = $this->createMock(IriConverterInterface::class),
        );

        $data = [
            'account' => 'Some account',
            'amount' => '123,65',
            'date' => '',
        ];
        $context = [
            'foo' => 'bar',
        ];

        $accountConverter
            ->expects($this->once())
            ->method('convert')
            ->with($data['account'], $context)
            ->willReturn($account = new Account())
        ;

        $converter
            ->expects($this->once())
            ->method('getIriFromItem')
            ->with($account)
            ->willReturn($accountIri = '/foo/bar/baz')
        ;

        $expected = [
            'account' => $accountIri,
            'amount' => 123.65,
        ];

        $this->assertEquals($expected, $preparer->prepare($data, $context));
    }
}
