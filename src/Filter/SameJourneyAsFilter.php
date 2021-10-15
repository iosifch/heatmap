<?php
declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\AbstractFilter;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Symfony\Component\PropertyInfo\Type;

final class SameJourneyAsFilter extends AbstractFilter
{
    protected function filterProperty(
        string $property,
        $value,
        Builder $aggregationBuilder,
        string $resourceClass,
        string $operationName = null,
        array &$context = []
    ): void {
        $aggregationBuilder
            ->match()
                ->field('_id')->equals($value)
            ->lookup('customers')
                ->alias('c2')
                ->localField('hashHistory')
                ->foreignField('hashHistory')
            ->unwind('$c2')
            ->addFields()
                ->field('_id')
                ->expression('$c2._id')
            ->match()
                ->field('_id')
                ->notEqual($value);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'same_journey_as' => [
                'type' => Type::BUILTIN_TYPE_STRING,
                'required' => false,
                'description' => 'Filter all customers that had the same journey',
            ]
        ];
    }
}
