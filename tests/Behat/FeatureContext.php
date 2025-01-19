<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Product\Domain\Product;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

final class FeatureContext implements Context
{
    private string $baseUrl;
    /** @var KernelInterface */
    private $kernel;

    /** @var Response|null */
    private $response;
    private EntityManagerInterface $entityManager;
    private array $sharedData = [];

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        $this->kernel = $kernel;
        $this->baseUrl = "http://localhost:8000";
        $this->entityManager = $entityManager;
    }

    /**
     * @BeforeScenario
     */
    public function setupDatabase(): void
    {
        // Drop and recreate the database schema
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }

    /**
     * @Given I have a Product :entity with data:
     */
    public function havingAProductWithData(string $entity, PyStringNode $jsonNode)
    {
        $data = json_decode($jsonNode->getRaw(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('El JSON proporcionado no es válido.');
        }
        $requiredFields = ['name', 'price', 'stock'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException(sprintf('Falta el campo requerido "%s" en el JSON.', $field));
            }
        }
        $product = Product::create($data['name'], (float)$data['price'], (int)$data['stock']);
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->sharedData[$entity] = $product->getId();
    }

    /**
     * @When I add a product to :cart with data:
     */
    public function addProductToCartWithData(string $cart, PyStringNode $jsonNode): void
    {
        $data = json_decode($jsonNode->getRaw(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('El JSON proporcionado no es válido.');
        }

        if (isset($data['productId'])) {
            $data['productId'] = $this->sharedData[$data['productId']];
        }
        if (isset($data['cartId'])) {
            $data['cartId'] = $this->sharedData[$data['cartId']];
        }
        $this->response = $this->kernel->handle(Request::create(
            $this->baseUrl . "/cart/add",
            "POST",
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        assertEquals($this->response->getStatusCode(), 200);

        $this->sharedData[$cart] = json_decode($this->response->getContent(), true)['cartId'];
    }

    /**
     * @When I cannot add a product to cart with data:
     */
    public function cannotAddProductToCartWithData(PyStringNode $jsonNode): void
    {
        $data = json_decode($jsonNode->getRaw(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('El JSON proporcionado no es válido.');
        }

        if (isset($data['productId'])) {
            $data['productId'] = $this->sharedData[$data['productId']];
        }
        $this->response = $this->kernel->handle(Request::create(
            $this->baseUrl . "/cart/add",
            "POST",
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        assertEquals($this->response->getStatusCode(), 409);
    }

    /**
     * @When I remove a product from :cart with data:
     */
    public function removeProductFromCartWithData(string $cart, PyStringNode $jsonNode): void
    {
        $data = json_decode($jsonNode->getRaw(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('El JSON proporcionado no es válido.');
        }
        $requiredFields = ['productId', 'cartId'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException(sprintf('Falta el campo requerido "%s" en el JSON.', $field));
            }
        }

        $data['productId'] = $this->sharedData[$data['productId']];
        $data['cartId'] = $this->sharedData[$data['cartId']];

        $this->response = $this->kernel->handle(Request::create(
            $this->baseUrl . "/cart/remove",
            "POST",
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        assertEquals($this->response->getStatusCode(), 200);
    }

    /**
     * @When I update a product of cart with data:
     */
    public function updateProductOfCartWithData(PyStringNode $jsonNode): void
    {
        $data = json_decode($jsonNode->getRaw(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('El JSON proporcionado no es válido.');
        }
        $requiredFields = ['productId', 'cartId', 'quantity'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException(sprintf('Falta el campo requerido "%s" en el JSON.', $field));
            }
        }

        $data['productId'] = $this->sharedData[$data['productId']];
        $data['cartId'] = $this->sharedData[$data['cartId']];

        $this->response = $this->kernel->handle(Request::create(
            $this->baseUrl . "/cart/update",
            "POST",
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        assertEquals($this->response->getStatusCode(), 200);
    }

    /**
     * @When I cannot update a product of cart with data:
     */
    public function cannotUpdateProductOfCartWithData(PyStringNode $jsonNode): void
    {
        $data = json_decode($jsonNode->getRaw(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('El JSON proporcionado no es válido.');
        }
        $requiredFields = ['productId', 'cartId', 'quantity'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException(sprintf('Falta el campo requerido "%s" en el JSON.', $field));
            }
        }

        $data['productId'] = $this->sharedData[$data['productId']];
        $data['cartId'] = $this->sharedData[$data['cartId']];

        $this->response = $this->kernel->handle(Request::create(
            $this->baseUrl . "/cart/update",
            "POST",
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        assertEquals($this->response->getStatusCode(), 409);
    }

    /**
     * @When I want to get total items of :cart
     */
    public function wantToGetTotalItemsOfCart(string $cart): void
    {
        $data = [];
        $data['cartId'] = $this->sharedData[$cart];
        $this->response = $this->kernel->handle(Request::create(
            $this->baseUrl . "/cart/totalItems",
            "POST",
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        assertEquals($this->response->getStatusCode(), 200);
        assertArrayHasKey('total', json_decode($this->response->getContent(), true));
    }

    /**
     * @Then the total items must be :quantity
     */
    public function totalItemsMustBe(int $quantity): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response received');
        }
        $data = json_decode($this->response->getContent(), true);
        assertEquals($data['total'], $quantity);
    }

    /**
     * @Then I confirm :cart
     */
    public function confirmCart(string $cart): void
    {
        $data = [];
        $data['cartId'] = $this->sharedData[$cart];
        $this->response = $this->kernel->handle(Request::create(
            $this->baseUrl . "/cart/confirm",
            "POST",
            $data,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        assertEquals($this->response->getStatusCode(), 200);
        assertArrayHasKey('orderId', json_decode($this->response->getContent(), true));
    }
}