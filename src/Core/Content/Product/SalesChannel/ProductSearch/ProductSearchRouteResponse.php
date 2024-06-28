<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Core\Content\Product\SalesChannel\ProductSearch;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class ProductSearchRouteResponse extends StoreApiResponse
{
    /**
     * @var EntitySearchResult<ProductCollection>
     */
    protected $object;

    /**
     * @param EntitySearchResult<ProductCollection> $object
     */
    public function __construct(EntitySearchResult $object)
    {
        parent::__construct($object);
    }

    public function getProducts(): ProductCollection
    {
        return $this->object->getEntities();
    }
}