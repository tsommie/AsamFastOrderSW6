<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Core\Content\Product\SalesChannel\ProductSearch;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

abstract class AbstractProductSearchRoute
{
    abstract public function getDecorated(): AbstractProductSearchRoute;

    abstract public function load(Criteria $criteria, SalesChannelContext $context): ProductSearchRouteResponse;
}