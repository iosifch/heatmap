<?php
declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Filter\SameJourneyAsFilter;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @Document(collection="customers")
 */
#[ApiResource(
    collectionOperations: ['GET'],
    itemOperations: ['GET'],
    denormalizationContext: ['groups' => ['write']],
    normalizationContext: ['groups' => ['read']]
)]
#[ApiFilter(SameJourneyAsFilter::class)]
class Customer
{
    /**
     * @Id()
     */
    #[Groups('read')]
    public string $id;

    /**
     * @Field(type="string")
     */
    public string $hashHistory;
}
