<?php

namespace App\Http\Controllers;

use App\Actions\LookupBrregEntityAction;
use App\Http\Requests\LookupBrregEntityRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BrregEntityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        LookupBrregEntityRequest $request,
        LookupBrregEntityAction $lookupBrregEntity,
    ): JsonResponse {
        try {
            $entity = $lookupBrregEntity->handle($request->string('organization_number')->toString());
        } catch (ConnectionException|RequestException) {
            return response()->json([
                'message' => __('brreg.lookup_unavailable'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if ($entity === null) {
            return response()->json([
                'message' => __('brreg.lookup_not_found'),
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $entity]);
    }
}
