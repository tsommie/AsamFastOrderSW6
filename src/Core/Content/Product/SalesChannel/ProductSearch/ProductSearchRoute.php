<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Core\Content\Product\SalesChannel\ProductSearch;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class ProductSearchRoute extends AbstractProductSearchRoute
{
    public function __construct(private readonly EntityRepository $productRepository)
    {}

    public function getDecorated(): AbstractProductSearchRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/fast-order/search', name: 'store-api.asam.fast-order.search', defaults: ['_entity' => 'product'], methods: ['GET', 'POST'])]
    public function load(Criteria $criteria, SalesChannelContext $context): ProductSearchRouteResponse
    {
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new RangeFilter('stock', [
            RangeFilter::GT => 0,
        ]));
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('childCount', 0),
            new EqualsFilter('childCount', null),
        ]));

        return new ProductSearchRouteResponse($this->productRepository->search($criteria, $context->getContext()));
    }
}