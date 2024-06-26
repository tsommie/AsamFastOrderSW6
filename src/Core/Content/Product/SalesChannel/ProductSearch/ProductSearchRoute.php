<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Core\Content\Product\SalesChannel\ProductSearch;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Attribute\Route;

class ProductSearchRoute extends AbstractProductSearchRoute
{
    public function __construct(private readonly SalesChannelRepository $productRepository)
    {}

    public function getDecorated(): AbstractProductSearchRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/fast-order/search', name: 'store-api.asam.fast-order.search', defaults: ['_entity' => 'product'], methods: ['GET', 'POST'])]
    public function load(Criteria $criteria, SalesChannelContext $context): ProductSearchRouteResponse
    {
        return new ProductSearchRouteResponse($this->productRepository->search($criteria, $context));
    }
}