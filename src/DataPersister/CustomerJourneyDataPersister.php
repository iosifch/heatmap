<?php
declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Document\Customer;
use App\Document\Hit;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use JsonException;
use RuntimeException;

final class CustomerJourneyDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private ContextAwareDataPersisterInterface $decorated,
        private DocumentManager $documentManager
    ) {
    }

    public function supports($data, array $context = []): bool
    {
        return $this->decorated->supports($data, $context);
    }

    /**
     * @throws MongoDBException
     * @throws JsonException
     */
    public function persist($data, array $context = [])
    {
        // use some transactions here
        if (!$data instanceof Hit) {
            throw new RuntimeException('');
        }

        if (!isset($data->customerId)) {
            $customer = new Customer();

            $customer->hashHistory = $this->generateHashOfHits([
                [
                    'link' => $data->link,
                    'linkType' => $data->linkType,
                ]
            ]);
            $this->documentManager->persist($customer);

            $data->customerId = $customer->id;

            $result = $this->decorated->persist($data, $context);
        } else {
            /** @var Customer $customer */
            $customer = $this->documentManager->getRepository(Customer::class)->find($data->customerId);

            if (!$customer instanceof Customer) {
                throw new RuntimeException('The provided customer id not exists');
            }

            $hits = $this->documentManager->createQueryBuilder(Hit::class)
                ->field('customerId')->equals($customer->id)
                ->sort('hitAt', 1)
                ->select(['link', 'linkType'])
                ->hydrate(false)
                ->getQuery()
                ->execute();

            $customer->hashHistory = $this->generateHashOfHits($hits->toArray());

            $this->documentManager->persist($customer);

            $result = $this->decorated->persist($data, $context);
        }

        return $result;
    }

    public function remove($data, array $context = [])
    {
        return $this->decorated->remove($data, $context);
    }

    /**
     * @throws JsonException
     */
    private function generateHashOfHits(array $hits): string
    {
        return md5(
            json_encode(
                array_map(static function (array $hit): array {
                    return [
                        $hit['link'], $hit['linkType']
                    ];
                }, $hits),
                JSON_THROW_ON_ERROR
            )
        );
    }
}
