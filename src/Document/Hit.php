<?php
declare(strict_types=1);

namespace App\Document;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Common\Filter\SearchFilterInterface;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\MongoDbOdm\Filter\SearchFilter;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Id;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Url;

/**
 * @Document(collection="hits")
 */
#[ApiResource(
    collectionOperations: [
        'GET',
        'POST',
        'count' => [
            'method' => 'GET',
            'path' => '/hits/count',
            'openapi_context' => [
                'responses' => [
                    '200' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'count' => [
                                            'type' => 'integer'
                                        ]
                                    ]
                                ]
                            ]
                        ]

                    ],
                ]
            ]
        ]
    ],
    itemOperations: [
        'GET',
    ],
    denormalizationContext: ['groups' => ['write']],
    normalizationContext: ['groups' => ['read']]
)]
#[ApiFilter(DateFilter::class, properties: ['hitAt'])]
#[ApiFilter(NumericFilter::class, properties: ['customerId'])]
#[ApiFilter(OrderFilter::class, properties: ['hitAt' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: [
    'link' => SearchFilterInterface::STRATEGY_EXACT,
    'linkType' => SearchFilterInterface::STRATEGY_EXACT
])]
class Hit
{
    private const LINK_TYPES = ['product', 'category', 'static-page', 'checkout', 'homepage'];
    /**
     * @Id()
     */
    #[Groups("read")]
    public string $id;

    /**
     * @Field(type="string")
     */
    #[Groups(["read", "write"])]
    #[NotBlank]
    #[NotNull]
    #[Url]
    public string $link;

    /**
     * @Field(type="string")
     */
    #[Groups(["read", "write"])]
    #[NotBlank]
    #[NotNull]
    #[Choice(choices: self::LINK_TYPES)]
    #[ApiProperty(
        attributes: [
            'openapi_context' => [
                'type' => 'string',
                'enum' => self::LINK_TYPES
            ]
        ]
    )]
    public string $linkType;

    /**
     * @Field(type="integer")
     */
    #[Groups(["read", "write"])]
    #[NotBlank]
    #[NotNull]
    #[Positive]
    public int $customerId;

    /**
     * @Field(type="date")
     */
    #[Groups("read")]
    public \DateTime $hitAt;

    public function __construct()
    {
        $this->hitAt = new \DateTime();
    }
}
