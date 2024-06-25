<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Storefront\Page\FastOrder;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class FastOrderPageLoader
{
    public function __construct(
        protected GenericPageLoaderInterface $genericPageLoader,
        protected EventDispatcherInterface $eventDispatcher
    )
    {}

    public function load(Request $request, SalesChannelContext $context): FastOrderPage
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page = FastOrderPage::createFrom($page);

        $this->eventDispatcher->dispatch(new FastOrderPageLoadedEvent($page, $context, $request));

        return $page;
    }
}