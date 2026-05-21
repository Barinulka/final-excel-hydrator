<?php

declare(strict_types=1);

namespace App\Controller\Api\ExcelExport;

use App\Application\ExcelExport\CreateExcelExport\ArchivedFinancialModelCannotBeExportedException;
use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportCommand;
use App\Application\ExcelExport\CreateExcelExport\CreateExcelExportHandler;
use App\Application\ExcelExport\CreateExcelExport\FinancialModelForExcelExportNotFoundException;
use App\Controller\Api\BaseApiAbstractController;
use App\Domain\Shared\ValueObject\ShortId;
use App\Presentation\Api\Response\ExcelExport\ExcelExportApiResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateExcelExportController extends BaseApiAbstractController
{
    #[Route(
        path: '/api/models/{financialModelShortId}/exports/excel',
        name: 'api.excel_export.create',
        requirements: [
            'financialModelShortId' => '[23456789abcdefghjkmnpqrstuvwxyz]{10}',
        ],
        methods: ['POST'],
    )]
    public function __invoke(
        string $financialModelShortId,
        CreateExcelExportHandler $handler,
        ExcelExportApiResponseFactory $responseFactory,
    ): JsonResponse {
        $owner = $this->getAuthorizedUser();

        $command = new CreateExcelExportCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
        );

        try {
            $result = $handler->handle($command);
        } catch (FinancialModelForExcelExportNotFoundException) {
            return $this->json(['error' => 'not_found'], Response::HTTP_NOT_FOUND);
        } catch (ArchivedFinancialModelCannotBeExportedException) {
            return $this->json(['error' => 'financial_model_already_archived'], Response::HTTP_CONFLICT);
        }

        return $this->json($responseFactory->create($result), Response::HTTP_OK);
    }
}
