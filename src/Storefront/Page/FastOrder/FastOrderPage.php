<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Storefront\Page\FastOrder;

use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Shopware\Storefront\Page\Page;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FastOrderPage extends Page
{
    public function __construct(
        protected GenericPageLoaderInterface $genericPageLoader,
        protected EventDispatcherInterface $eventDispatcher
    )
    {}
}