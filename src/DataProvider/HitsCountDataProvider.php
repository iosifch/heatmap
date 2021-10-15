<?php
declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Extension\FilterExtension;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Document\Hit;
use Doctrine\ODM\MongoDB\DocumentManager;

final class HitsCountDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(private DocumentManager $documentManager, private FilterExtension $filterExtension)
    {
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return $resourceClass === Hit::class && $operationName === 'count';
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $aggregationBuilder = $this->documentManager->createAggregationBuilder($resourceClass);

        $this->filterExtension->applyToCollection($aggregationBuilder, $resourceClass, $operationName, $context);

        return ['count' => iterator_to_array($aggregationBuilder->count('total')->getAggregation())[0]['total'] ?? 0];
    }
}