<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use App\Entity\Transaction;
use App\Serializer\EntitySerializer;
use App\Serializer\EntitySerializerContextBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class EntitySerializerTest extends TestCase
{
    private EntitySerializer $serializer;
    /** @var \Symfony\Component\Serializer\Serializer|\PHPUnit\Framework\MockObject\MockObject */
    private Serializer $baseSerializer;
    /** @var \App\Serializer\EntitySerializerContextBuilder|\PHPUnit\Framework\MockObject\MockObject */
    private EntitySerializerContextBuilder $serializerContextBuilder;

    /**
     * @covers \App\Serializer\EntitySerializer::__construct
     * @covers \App\Serializer\EntitySerializer::createContext()
     *
     * @throws \ApiPlatform\Core\Exception\ResourceClassNotFoundException
     */
    public function testCreateContext(): void
    {
        $this->serializerContextBuilder
            ->expects($this->once())
            ->method('create')
            ->with(false, $resourceClass = Transaction::class, $operationName = 'get', $format = 'csv')
            ->willReturn($expected = ['expected' => 'result']);

        $this->assertSame($expected, $this->serializer->createContext($resourceClass, $operationName, $format));
    }

    ///**
    // * @covers \App\Serializer\EntitySerializer::__construct
    // * @covers \App\Serializer\EntitySerializer::decode()
    // */
    //public function testDecode(): void
    //{
    //    $this->baseSerializer
    //        ->expects($this->once())
    //        ->method('decode')
    //        ->with($data = 'foo,bar', $format = 'csv', $context = ['some' => 'var'])
    //        ->willReturn($expected = ['expected' => 'result']);
    //
    //    $this->assertSame($expected, $this->serializer->decode($data, $format, $context));
    //}

    /**
     * @covers \App\Serializer\EntitySerializer::__construct
     * @covers \App\Serializer\EntitySerializer::denormalize()
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function testDenormalize(): void
    {
        $this->baseSerializer
            ->expects($this->once())
            ->method('denormalize')
            ->with($data = ['foo' => 'bar'], $resourceClass = Transaction::class, $format = 'csv', $context = ['some' => 'var'])
            ->willReturn($expected = new Transaction());

        $this->assertSame($expected, $this->serializer->denormalize($data, $resourceClass, $format, $context));
    }

    protected function setUp(): void
    {
        $this->serializer = new EntitySerializer(
            $this->baseSerializer = $this->createMock(Serializer::class),
            $this->serializerContextBuilder = $this->createMock(EntitySerializerContextBuilder::class),
        );
    }
}
