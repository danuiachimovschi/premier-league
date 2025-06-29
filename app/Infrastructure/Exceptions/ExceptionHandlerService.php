<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use App\Domain\Exceptions\InvalidUuidException;
use App\Domain\Exceptions\MatchException;
use App\Domain\Exceptions\SeasonException;
use App\Domain\Exceptions\TeamException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionHandlerService
{
    public static function register(Exceptions $exceptions): void
    {
        self::handleInvalidUuidExceptions($exceptions);
        self::handleModelNotFoundExceptions($exceptions);
        self::handleDomainExceptions($exceptions);
        self::handleValidationExceptions($exceptions);
        self::handleGeneralExceptions($exceptions);
        self::handleNotFoundExceptions($exceptions);
    }

    private static function handleInvalidUuidExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (InvalidUuidException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found',
                    'errors' => null,
                ], Response::HTTP_NOT_FOUND);
            }
        });
    }

    private static function handleModelNotFoundExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found',
                    'errors' => null,
                ], Response::HTTP_NOT_FOUND);
            }
        });
    }

    private static function handleDomainExceptions(Exceptions $exceptions): void
    {
        self::handleSeasonExceptions($exceptions);
        self::handleMatchExceptions($exceptions);
        self::handleTeamExceptions($exceptions);
    }

    private static function handleSeasonExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (SeasonException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => null,
                ], Response::HTTP_BAD_REQUEST);
            }
        });
    }

    private static function handleMatchExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (MatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => null,
                ], Response::HTTP_BAD_REQUEST);
            }
        });
    }

    private static function handleTeamExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (TeamException $e, Request $request) {
            if ($request->expectsJson()) {
                // If it's a not found exception, return 404
                if (str_contains($e->getMessage(), 'not found')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage(),
                        'errors' => null,
                    ], Response::HTTP_NOT_FOUND);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => null,
                ], Response::HTTP_BAD_REQUEST);
            }
        });
    }

    private static function handleValidationExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        });
    }

    private static function handleGeneralExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (\InvalidArgumentException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => null,
                ], Response::HTTP_BAD_REQUEST);
            }
        });

        $exceptions->render(function (\RuntimeException $e, Request $request) {
            if ($request->expectsJson()) {
                // Check if it's a "not found" type message
                if (str_contains(strtolower($e->getMessage()), 'not found') ||
                    str_contains(strtolower($e->getMessage()), 'no matches found')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage(),
                        'errors' => null,
                    ], Response::HTTP_NOT_FOUND);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => null,
                ], Response::HTTP_BAD_REQUEST);
            }
        });
    }

    private static function handleNotFoundExceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Resource not found',
                    'errors' => null,
                ], Response::HTTP_NOT_FOUND);
            }
        });
    }
}