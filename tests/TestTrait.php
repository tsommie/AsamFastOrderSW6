<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Tests;

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Test\TestDefaults;
use Shopware\Storefront\Test\Controller\StorefrontControllerTestBehaviour;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait TestTrait
{

    use IntegrationTestBehaviour;
    use StorefrontControllerTestBehaviour;
    use KernelTestBehaviour;
    use SalesChannelApiTestBehaviour;

    private Context $context;

    private string $productNumber;

    final const PRODUCT_NUMBER = 'TEST';

    protected ProductEntity $product;

    protected ?EntityRepository $productRepository;

    protected KernelBrowser $browser;

    protected function initializeProperties(): void
    {
        $this->context = Context::createDefaultContext();
        $this->productRepository = $this->getContainer()->get('product.repository');
        $this->product = $this->createProduct();
    }

    public function cleanUp(): void
    {
        $this->deleteProduct();
    }

    protected function createProduct(): ?ProductEntity
    {
        $productId = Uuid::randomHex();

        $product = [
            'id' => $productId,
            'productNumber' => static::PRODUCT_NUMBER,
            'stock' => 100,
            'name' => 'Test product',
            'ean' => 'test',
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 10, 'net' => 10, 'linked' => false],
            ],
            'manufacturer' => ['name' => 'Test'],
            'tax' => ['name' => 'test', 'taxRate' => 15],
            'active' => true,
            'visibilities' => [
                ['salesChannelId' => TestDefaults::SALES_CHANNEL, 'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL],
            ],
        ];

        $this->productRepository->upsert([$product], $this->context);

        /** @var ?ProductEntity $product */
        $product = $this->productRepository->search(
            (new Criteria([$productId]))->setLimit(1),
            $this->context
        )->first();

        static::assertNotNull($product, 'Product not found');

        return $product;
    }

    protected function deleteProduct(): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', static::PRODUCT_NUMBER));

        $productId = $this->productRepository->searchIds($criteria, $this->context)->firstId();

        if ($productId === null) {
            return;
        }

        $this->productRepository->delete([['id' => $productId]], $this->context);
    }
}