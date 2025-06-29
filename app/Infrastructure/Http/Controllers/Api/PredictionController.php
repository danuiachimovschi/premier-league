<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Api;

use App\Domain\Contracts\Services\PredictionServiceInterface;
use App\Domain\Models\Season;
use App\Infrastructure\Http\Transformers\PredictionTransformer;
use App\Infrastructure\Http\Transformers\SeasonTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class PredictionController extends Controller
{
    public function __construct(
        private readonly PredictionServiceInterface $predictionService
    ) {}

    public function index(Season $season): JsonResponse
    {
        $data = $this->predictionService->getPredictions($season);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Success',
            'data' => [
                'season' => new SeasonTransformer($season),
                'predictions' => PredictionTransformer::collection($data['predictions']),
                'history' => $data['history'],
                'analysis' => $data['analysis'],
            ],
        ], Response::HTTP_OK);
    }

}