<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Infrastructure\Http\Controllers\Api\BaseController;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class TestableBaseController extends BaseController
{
    public function testSuccessResponse(mixed $data, string $message = 'Success', int $code = 200): JsonResponse
    {
        return $this->successResponse($data, $message, $code);
    }
    
    public function testErrorResponse(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        return $this->errorResponse($message, $code, $errors);
    }
}

class BaseControllerTest extends TestCase
{
    private TestableBaseController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TestableBaseController();
    }

    public function test_success_response_returns_correct_structure(): void
    {
        $data = ['test' => 'data'];
        $message = 'Operation successful';
        $code = 200;

        $response = $this->controller->testSuccessResponse($data, $message, $code);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($code, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_success_response_with_default_parameters(): void
    {
        $data = ['test' => 'data'];

        $response = $this->controller->testSuccessResponse($data);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Success', $responseData['message']);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_success_response_with_custom_status_code(): void
    {
        $data = ['id' => 1, 'name' => 'Created Item'];
        $message = 'Item created successfully';
        $code = 201;

        $response = $this->controller->testSuccessResponse($data, $message, $code);

        $this->assertEquals(201, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_error_response_returns_correct_structure(): void
    {
        $message = 'Validation failed';
        $code = 400;
        $errors = ['name' => ['The name field is required.']];

        $response = $this->controller->testErrorResponse($message, $code, $errors);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($code, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals($message, $responseData['message']);
        $this->assertEquals($errors, $responseData['errors']);
    }

    public function test_error_response_with_default_parameters(): void
    {
        $message = 'Something went wrong';

        $response = $this->controller->testErrorResponse($message);

        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals($message, $responseData['message']);
        $this->assertNull($responseData['errors']);
    }

    public function test_error_response_without_errors(): void
    {
        $message = 'Resource not found';
        $code = 404;

        $response = $this->controller->testErrorResponse($message, $code);

        $this->assertEquals(404, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals($message, $responseData['message']);
        $this->assertNull($responseData['errors']);
    }

    public function test_success_response_with_null_data(): void
    {
        $response = $this->controller->testSuccessResponse(null);

        $responseData = $response->getData(true);
        $this->assertNull($responseData['data']);
        $this->assertEquals('success', $responseData['status']);
    }

    public function test_success_response_with_empty_array_data(): void
    {
        $response = $this->controller->testSuccessResponse([]);

        $responseData = $response->getData(true);
        $this->assertEquals([], $responseData['data']);
        $this->assertEquals('success', $responseData['status']);
    }

    public function test_error_response_with_string_errors(): void
    {
        $message = 'Validation failed';
        $errors = 'Invalid input provided';

        $response = $this->controller->testErrorResponse($message, 400, $errors);

        $responseData = $response->getData(true);
        $this->assertEquals($errors, $responseData['errors']);
    }

    public function test_success_response_with_complex_data_structure(): void
    {
        $data = [
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'emails' => ['john@example.com', 'doe@example.com']
            ],
            'metadata' => [
                'created_at' => '2023-01-01T00:00:00Z',
                'updated_at' => '2023-01-02T00:00:00Z'
            ]
        ];

        $response = $this->controller->testSuccessResponse($data);

        $responseData = $response->getData(true);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_response_headers_are_json(): void
    {
        $response = $this->controller->testSuccessResponse(['test' => 'data']);

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function test_error_response_with_validation_errors_array(): void
    {
        $errors = [
            'name' => ['The name field is required.'],
            'email' => ['The email field must be a valid email address.']
        ];

        $response = $this->controller->testErrorResponse('Validation failed', 422, $errors);

        $responseData = $response->getData(true);
        $this->assertEquals($errors, $responseData['errors']);
        $this->assertEquals(422, $response->getStatusCode());
    }
}