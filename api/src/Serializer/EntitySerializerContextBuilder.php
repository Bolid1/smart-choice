<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Core\Api\OperationType;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\Swagger\Serializer\DocumentationNormalizer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class EntitySerializerContextBuilder
{
    private ResourceMetadataFactoryInterface $resourceMetadataFactory;

    public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory)
    {
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    /**
     * @param bool $normalization is normalization context or denormalization context
     * @param string $resourceClass class to process
     * @param string $operationName Name of operation, f.e. get, put, patch
     * @param string $format Format of data, f.e. csv, json, jsonld
     *
     * @return array
     *
     * @throws \ApiPlatform\Core\Exception\ResourceClassNotFoundException
     */
    public function create(bool $normalization, string $resourceClass, string $operationName, string $format): array
    {
        $operationKey = 'item_operation_name';
        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
        $operationType = OperationType::ITEM;

        $context = $resourceMetadata->getTypedOperationAttribute(
            $operationType,
            $operationName,
            $normalization ? 'normalization_context' : 'denormalization_context',
            [],
            true
        );

        $myContext = [
            'operation_type' => $operationType,
            $operationKey => $operationName,
            'resource_class' => $resourceClass,
            'input' => $resourceMetadata->getTypedOperationAttribute(
                $operationType,
                $operationName,
                'input',
                null,
                true
            ),
            'output' => $resourceMetadata->getTypedOperationAttribute(
                $operationType,
                $operationName,
                'output',
                null,
                true
            ),
        ];

        if (!$normalization) {
            if (!isset($context['api_allow_update'])) {
                $context['api_allow_update'] = \in_array(\strtoupper($operationName), ['PUT', 'PATCH', 'EDIT'], true);
            }

            if ('csv' === $format) {
                $context[CsvEncoder::AS_COLLECTION_KEY] = false;
            }
        }

        unset($context[DocumentationNormalizer::SWAGGER_DEFINITION_NAME]);

        return $myContext + $context;
    }
}
