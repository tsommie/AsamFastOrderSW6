<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Tests\Core\Content\Product\SalesChannel\ProductSearch;

use Asam\FastOrderSW6\Tests\TestTrait;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Test\TestDataCollection;

class ProductSearchRouteTest extends TestCase
{
    use TestTrait;
    use SalesChannelApiTestBehaviour;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeProperties();

        $this->createSalesChannelContext(['id' => (new TestDataCollection())->create('sales-channel')]);
        $this->browser = $this->getSalesChannelBrowser();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUp();
    }

    public function testLoad()
    {
        $this->browser->request(
            'POST',
            '/store-api/fast-order/search',
        );

        $response = $this->browser->getResponse();

        $code = $response->getStatusCode();
        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame(200, $code);
        static::assertArrayHasKey('elements', $content);
        static::assertNotEmpty($content['elements']);
    }
}
