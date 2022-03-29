<?php

namespace SultanovSolutions\LaravelBase\Controllers;

use SultanovSolutions\LaravelBase\Services\HttpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelBaseController;

abstract class RestController extends LaravelBaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * Main Endpoint, also used as Scope
     * @var string|null
     */
    protected ?string $endpoint;

    /**
     * @var HttpService|null
     */
    private ?HttpService $httpService;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->httpService = new HttpService($request);
            //$this->httpService->setBaseUrl('my-api');
            return $next($request);
        });
    }

    ##############################
    # Main interfaces
    ##############################
    /**
     * Main get all interface
     * @return JsonResponse
     */
    public function all(): JsonResponse
    {
        $response = $this->httpService->requestHttp->get($this->endpoint . '/all');

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }

    /**
     * Main paginated list interface
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $requestResponse = $this->httpService->requestHttp->get($this->endpoint, $request->query->all());
        return $this->httpService->hydratePaginationResponse($requestResponse);
    }

    /**
     * Main read interface
     * @param $userId
     * @return JsonResponse
     */
    public function read($userId): JsonResponse
    {
        $response = $this->httpService->requestHttp->get($this->endpoint . '/' . $userId);

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }

    /**
     * Main create interface
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $response = $this->httpService->requestHttp->post($this->endpoint, $request);

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }

    /**
     * Main update interface
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $response = $this->httpService->requestHttp->put($this->endpoint, $request);

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }


    /**
     * Main destroy interface
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $response = $this->httpService->requestHttp->delete($this->endpoint, $request);

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }
}
