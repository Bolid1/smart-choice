<?php

declare(strict_types=1);

namespace App\Tests\Converter;

use App\Converter\AccountConverter;
use App\Entity\Account;
use App\Entity\Company;
use PHPUnit\Framework\TestCase;

class AccountConverterTest extends TestCase
{
    /**
     * @covers \App\Converter\AccountConverter::convert
     */
    public function testConvert(): void
    {
        $converter = new AccountConverter();

        $company = $this->createMock(Company::class);
        $company
            ->expects($this->once())
            ->method('getAccountById')
            ->with($id = 'foo bar')
        ;
        $company
            ->expects($this->once())
            ->method('getAccountByName')
            ->with($id)
            ->willReturn($account = new Account())
        ;

        $this->assertSame($account, $converter->convert($id, \compact('company')));
    }
}
