<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Storefront\Page\FastOrder;

use Shopware\Storefront\Page\PageLoadedEvent;

class FastOrderPageLoadedEvent extends PageLoadedEvent
{
    public function __construct(
        protected FastOrderPage $page,
        protected               $salesChannelContext,
        protected               $request
    )
    {
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): FastOrderPage
    {
        return $this->page;
    }
}