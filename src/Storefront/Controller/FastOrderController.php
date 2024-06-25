<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Storefront\Controller;

use Asam\FastOrderSW6\Storefront\Page\FastOrder\FastOrderPageLoader;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\Exception\StorefrontException;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
    public function __construct(
        protected FastOrderPageLoader $fastOrderPageLoader
    )
    {}

    /**
     * @throws StorefrontException
     */
    #[Route(
        path: '/fast-order',
        name: 'frontend.asam.fast-order.form',
        methods: ['GET']
    )]
    public function form(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->fastOrderPageLoader->load($request, $context);

        return $this->renderStorefront(
            '@Storefront/storefront/page/fast-order/fast-order-form/index.html.twig',
            compact('page')
        );
    }
}
