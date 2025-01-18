<?php

namespace App\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class ApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * returns success response with code 200.
     *
     * @param JsonResource $resource
     * @param string $message
     *
     * @return Response
     */
    protected function successResponse(JsonResource|array $resource, string $message = ''): Response
    {
        $response = [
            'status' => 'success',
            'data' => $resource
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response($response, ResponseAlias::HTTP_OK);
    }

    /**
     * Returns the error responses with code 400.
     *
     * @param string $error
     * @param int $code
     * @param \Exception|null $exception
     *
     * @return Response
     */
    protected function errorResponse(string $error, \Exception|null $exception = null, int $code = ResponseAlias::HTTP_BAD_REQUEST): Response
    {
        Log::error($exception?->getTraceAsString(), ['Method' => request()->route()->getActionName()]);
        return response(new JsonResource(['success' => false, 'message' => $error]), $code);
    }

    public function guard()
    {
        return Auth::guard('api');
    }
}
