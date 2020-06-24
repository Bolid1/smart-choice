<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\Serializer\Encoder\ContextAwareDecoderInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class EntitySerializer
{
    private EntitySerializerContextBuilder $serializerContextBuilder;
    private Serializer $serializer;

    /**
     * EntitySerializer constructor.
     *
     * @param Serializer|SerializerInterface|ContextAwareDecoderInterface $serializer
     * @param EntitySerializerContextBuilder $serializerContextBuilder
     */
    public function __construct(
        SerializerInterface $serializer,
        EntitySerializerContextBuilder $serializerContextBuilder
    ) {
        $this->serializerContextBuilder = $serializerContextBuilder;
        $this->serializer = $serializer;
    }

    /**
     * @param string $resourceClass
     * @param string $operationName
     * @param string $format
     *
     * @return array
     *
     * @throws \ApiPlatform\Core\Exception\ResourceClassNotFoundException
     */
    public function createContext(string $resourceClass, string $operationName, string $format): array
    {
        return $this->serializerContextBuilder->create(false, $resourceClass, $operationName, $format);
    }

    /**
     * @param string $data
     * @param string $format
     * @param array $context
     *
     * @return array
     */
    public function decode(string $data, string $format, array $context): array
    {
        return $this->serializer->decode($data, $format, $context) ?: [];
    }

    /**
     * @param $data
     * @param string $resourceClass
     * @param string $format
     * @param array $context
     *
     * @return object|null
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function denormalize($data, string $resourceClass, string $format, array $context): ?object
    {
        return $this->serializer->denormalize($data, $resourceClass, $format, $context);
    }
}
