<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\PredictionServiceInterface;
use App\Domain\Models\Season;
use App\Infrastructure\Http\Resources\PredictionResource;
use App\Infrastructure\Http\Resources\SeasonResource;
use Exception;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class PredictionController extends BaseController
{
    public function __construct(
        private readonly PredictionServiceInterface $predictionService
    ) {}

    public function index(Season $season): JsonResponse
    {
        try {
            $data = $this->predictionService->getPredictions($season);
            
            return $this->successResponse([
                'season' => new SeasonResource($season),
                'predictions' => PredictionResource::collection($data['predictions']),
                'history' => $data['history'],
                'analysis' => $data['analysis'],
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to get predictions: ' . $e->getMessage(), 500);
        }
    }

}