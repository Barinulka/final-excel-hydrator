<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Response;

use App\Presentation\Api\Response\ValidationErrorResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

final class ValidationErrorResponseFactoryTest extends TestCase
{
    public function testCreatesValidationErrorResponseGroupedByField(): void
    {
        $violations = new ConstraintViolationList([
            $this->createViolation('investmentStartMonth', 'Не указана дата начала инвестиций.'),
            $this->createViolation('investmentStartMonth', 'Формат даты должен быть YYYY-MM.'),
            $this->createViolation('forecastStep', 'Недопустимый шаг прогнозирования.'),
        ]);

        $response = (new ValidationErrorResponseFactory())->create($violations);

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $content = $response->getContent();
        self::assertIsString($content);

        $payload = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        self::assertSame([
            'error' => 'validation_failed',
            'fields' => [
                'investmentStartMonth' => [
                    'Не указана дата начала инвестиций.',
                    'Формат даты должен быть YYYY-MM.',
                ],
                'forecastStep' => [
                    'Недопустимый шаг прогнозирования.',
                ],
            ],
        ], $payload);
    }

    private function createViolation(string $propertyPath, string $message): ConstraintViolation
    {
        return new ConstraintViolation(
            message: $message,
            messageTemplate: null,
            parameters: [],
            root: null,
            propertyPath: $propertyPath,
            invalidValue: null,
        );
    }
}
