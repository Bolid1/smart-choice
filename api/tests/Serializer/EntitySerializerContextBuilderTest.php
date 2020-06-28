<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use App\Entity\Transaction;
use App\Serializer\EntitySerializerContextBuilder;
use App\Test\ApiTestCase;

class EntitySerializerContextBuilderTest extends ApiTestCase
{
    private EntitySerializerContextBuilder $builder;

    protected function setUp(): void
    {
        static::bootKernel();
        /* @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->builder = static::$container->get(EntitySerializerContextBuilder::class);
    }

    /**
     * @covers \App\Serializer\EntitySerializerContextBuilder::__construct
     * @covers \App\Serializer\EntitySerializerContextBuilder::create
     */
    public function testCreate(): void
    {
        $expected = [
            'operation_type' => 'item',
            'item_operation_name' => 'post',
            'resource_class' => Transaction::class,
            'input' => null,
            'output' => null,
            'groups' => [
                    0 => 'transaction:edit',
                ],
            'api_allow_update' => false,
        ];

        $result = $this->builder->create(false, Transaction::class, 'post', 'jsonld');

        $this->assertEquals($expected, $result);
    }
}
