<?php declare(strict_types=1);

namespace Asam\FastOrderSW6\Tests\Storefront\Controller;

use Asam\FastOrderSW6\Tests\TestTrait;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;

class FastOrderControllerTest extends TestCase
{
    use TestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->browser = KernelLifecycleManager::createBrowser($this->getKernel());
        $this->initializeProperties();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUp();
    }

    public function testFastOrderFormPage(): void
    {
        $this->browser->request('GET', $_SERVER['APP_URL'] . '/fast-order');

        $response = $this->browser->getResponse();

        static::assertEquals(200, $response->getStatusCode());
    }

    public function testFastOrderFormWithValidData(): void
    {
        $this->browser->request('POST', $_SERVER['APP_URL'] . '/fast-order', [
            'items' => [
                [
                    'number' => static::PRODUCT_NUMBER,
                    'quantity' => 1,
                ]
            ]
        ]);

        $response = $this->browser->getResponse();
        $code = $response->getStatusCode();
        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertEquals(200, $code);
        static::assertSame(true, $content['success']);
        static::assertSame('/checkout/cart', $content['redirectTo']);
    }

    public function testFastOrderFormPostWithInvalidProductNumber(): void
    {
        $this->browser->request('POST', $_SERVER['APP_URL'] . '/fast-order', [
            'items' => [
                [
                    'number' => 'INVALID',
                    'quantity' => 1,
                ]
            ]
        ]);


        $response = $this->browser->getResponse();
        $code = $response->getStatusCode();
        $content = json_decode((string) $response->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertEquals(200, $code);
        static::assertSame(false, $content['success']);
    }

    public function testFastOrderFormPostWithInvalidQuantity(): void
    {
        $this->browser->request('POST', $_SERVER['APP_URL'] . '/fast-order', [
            'items' => [
                [
                    'number' => self::PRODUCT_NUMBER,
                    'quantity' => 0,
                ]
            ]
        ]);

        $response = $this->browser->getResponse();

        static::assertEquals(200, $response->getStatusCode());
        $response = json_decode((string) $this->browser->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        static::assertSame(false, $response['success']);
    }

    public function testSearchOptions()
    {
        $this->browser->request('GET', $_SERVER['APP_URL'] . '/fast-order/search', [
            'productNumber' => static::PRODUCT_NUMBER
        ]);

        $response = $this->browser->getResponse();
        $code = $response->getStatusCode();
        $content = $response->getContent();

        static::assertEquals(200, $code);
        static::assertStringContainsString('value="TEST"', $content);
    }

    public function testSearchValidation()
    {
        $this->browser->request('GET', $_SERVER['APP_URL'] . '/fast-order/search', [
            'productNumber' => static::PRODUCT_NUMBER,
            'validationMode' => true
        ]);

        $response = $this->browser->getResponse();
        $code = $response->getStatusCode();
        $content = json_decode(
            (string) $response->getContent(),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );

        static::assertEquals(200, $code);
        static::assertIsArray($content);
        static::assertSame(true, $content['valid']);
        static::assertSame(static::PRODUCT_NUMBER, $content['product']['productNumber']);
    }

    public function testInputTemplateCanBeRetrieved()
    {
        $this->browser->request('GET', $_SERVER['APP_URL'] . '/fast-order/input/template');

        $response = $this->browser->getResponse();
        $code = $response->getStatusCode();
        $content = $response->getContent();

        static::assertEquals(200, $code);
        static::assertStringContainsString('data-asam-fast-order-form-input', $content);
    }
}