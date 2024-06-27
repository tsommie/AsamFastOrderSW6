<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Storefront\Controller;

use Asam\FastOrderSW6\Core\Content\Product\SalesChannel\ProductSearch\ProductSearchRoute;
use Asam\FastOrderSW6\Exception\InvalidProductIdException;
use Asam\FastOrderSW6\Storefront\Page\FastOrder\FastOrderPageLoader;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\Exception\StorefrontException;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
    public function __construct(
        protected FastOrderPageLoader $fastOrderPageLoader,
        protected ProductSearchRoute $productSearchRoute,
        protected readonly CartService $cartService,
        protected readonly LineItemFactoryRegistry $lineItemFactoryRegistry,
        protected readonly SystemConfigService $systemConfigService
    )
    {}

    /**
     * Renders the fast order form page.
     *
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

    /**
     * Renders the fast order form input template.
     *
     * @param Request $request
     * @param SalesChannelContext $context
     * @return Response
     */
    #[Route(
        path: '/fast-order/input/template',
        name: 'frontend.asam.fast-order.input-template',
        defaults: ['XmlHttpRequest' => true],
        methods: ['GET']
    )]
    public function inputTemplate(Request $request, SalesChannelContext $context): Response
    {
        return $this->renderStorefront(
            '@Storefront/storefront/page/fast-order/fast-order-form/fast-order-form-input.html.twig'
        );
    }

    /**
     * Handles product search and validation AJAX requests.
     *
     * @param Request $request
     * @param SalesChannelContext $context
     * @return Response
     */
    #[Route(
        path: '/fast-order/search',
        name: 'frontend.asam.fast-order.search',
        defaults: ['XmlHttpRequest' => true],
        methods: ['POST', 'GET']
    )]
    public function search(Request $request, SalesChannelContext $context): Response
    {
        $number = (string) $request->get('productNumber');
        $validationMode = (bool) $request->get('validationMode', false);

        $criteria = $this->getCriteria($number, $validationMode, $context);

        $products = $this->productSearchRoute->load($criteria, $context)->getProducts();

        // If validation mode is enabled, we only need to check if the product exists and then return a json response.
        if ($validationMode) {
            if ($products->count() > 0) {
                $product = $products->first();

                $response = [
                    'valid'     => true,
                    'productId' => $products->first()->getId(),
                    'message'   => null,
                    'product'   => [
                        'id'            => $product->getId(),
                        'productNumber' => $product->getProductNumber(),
                        'minPurchase'   => $product->getMinPurchase(),
                        'maxPurchase'   => $product->getMinPurchase(),
                        'purchaseSteps' => $product->getPurchaseSteps(),
                        'stock'         => $product->getAvailableStock(),
                    ]
                ];
            } else {
                $response = [
                    'valid'     => false,
                    'productId' => null,
                    'message'   => 'Product not found. Please enter a valid product number.',
                    'product'   => null
                ];
            }

            return $this->json($response);
        }

        return $this->renderStorefront(
            '@Storefront/storefront/page/fast-order/fast-order-form/fast-order-form-input-select-option.html.twig',
            compact('products')
        );
    }

    /**
     * @param Cart $cart
     * @param RequestDataBag $requestDataBag
     * @param Request $request
     * @param SalesChannelContext $context
     * @return JsonResponse
     */
    #[Route(
        path: '/fast-order',
        name: 'frontend.asam.fast-order.submit',
        defaults: ['XmlHttpRequest' => true],
        methods: ['POST'])
    ]
    public function addToCart(
        Cart $cart,
        RequestDataBag $requestDataBag,
        Request $request,
        SalesChannelContext $context
    ): JsonResponse
    {
        /** @var RequestDataBag|null $items */
        $items = $requestDataBag->get('items');
        if (!$items) {
            throw RoutingException::missingRequestParameter('items');
        }

        $count = 0;

        try {
            $lineItems = [];

            /** @var RequestDataBag $itemData */
            foreach ($items as $itemData) {
                try {
                    $product = $this->productSearchRoute->load(
                        $this->getCriteria($itemData->get('number'), true, $context),
                        $context
                    )->getProducts()->first();

                    if (!$product) {
                        /* @todo: Handle validations in a dedicated validation factory. */
                        throw new InvalidProductIdException(
                            $this->trans('asam.fastOrder.errors.invalidProductNumber', ['%number%' => $itemData->get('number')])
                        );
                    }

                    $itemData->set('id', $product->getId());
                    $item = $this->lineItemFactoryRegistry->create(
                        $this->getLineItemArray($itemData),
                        $context
                    );
                    $count += $item->getQuantity();

                    $lineItems[] = $item;
                } catch (CartException $e) {
                    if ($e->getErrorCode() === CartException::CART_INVALID_LINE_ITEM_QUANTITY_CODE) {
                        return $this->json([
                            'success' => false,
                            'errors' => [$this->trans(
                                'error.CHECKOUT__CART_INVALID_LINE_ITEM_QUANTITY',
                                [
                                    '%quantity%' => $e->getParameter('quantity'),
                                ]
                            )],
                            'redirectTo' => null,
                        ]);
                    }

                    throw $e;
                }
            }

            $cart = $this->cartService->add($cart, $lineItems, $context);

            if (!$this->traceErrors($cart)) {
                $this->addFlash(self::SUCCESS, $this->trans('checkout.addToCartSuccess', ['%count%' => $count]));
            }

            $response = [
                'success'       => true,
                'redirectTo'    => $this->generateUrl('frontend.checkout.cart.page'),
                'error'         => [],
            ];
        } catch (ProductNotFoundException|RoutingException) {
            $response = [
                'success'       => false,
                'errors'        => $cart->getErrors()->getElements(),
                'redirectTo'    => null,
            ];
        } catch (InvalidProductIdException $e) {
            $response = [
                'success'       => false,
                'errors'        => [$e->getMessage()],
                'redirectTo'    => null,
            ];
        } catch (\Throwable $e) {
            $response = [
                'success'       => false,
                'errors'        => [$this->trans('asam.fastOrder.errors.invalidFormData')],
                'redirectTo'    => null,
            ];
        }

        return $this->json($response);
    }

    /**
     * Get the criteria for searching and validating the product number.
     *
     * @param string $number
     * @param bool $validationMode
     * @param SalesChannelContext $context
     * @return Criteria
     */
    protected function getCriteria(string $number, bool $validationMode, SalesChannelContext $context): Criteria
    {
        $criteria = new Criteria();

        // To validate a product number, we need to use an exact match.
        if ($validationMode) {
            $criteria->addFilter(new EqualsFilter('productNumber', $number));
            $criteria->setLimit(1);
        } else {
            // Get the suggestion limit from the system configuration.
            $suggestionLimit = (int) $this->systemConfigService->get(
                'AsamFastOrderSW6.config.suggestionLimit',
                $context->getSalesChannel()->getId()
            );

            $criteria->addFilter(new ContainsFilter('productNumber', $number));
            $criteria->setLimit(
                $suggestionLimit > 0 ? $suggestionLimit : 10
            );
        }

        return $criteria;
    }

    /**
     * Prepare the line item array for adding to the cart and ensure the correct data types.
     *
     * @param RequestDataBag $lineItemData
     * @return array<string|int, mixed>
     */
    protected function getLineItemArray(RequestDataBag $lineItemData): array
    {
        $lineItemArray = $lineItemData->all();

        if (isset($lineItemArray['quantity'])) {
            $lineItemArray['quantity'] = (int) $lineItemArray['quantity'];
        }

        $lineItemArray['type'] = LineItem::PRODUCT_LINE_ITEM_TYPE;
        $lineItemArray['stackable'] = true;
        $lineItemArray['removable'] = true;

        return $lineItemArray;
    }

    private function traceErrors(Cart $cart): bool
    {
        if ($cart->getErrors()->count() <= 0) {
            return false;
        }

        $this->addCartErrors($cart, fn (Error $error) => $error->isPersistent());

        return true;
    }
}
