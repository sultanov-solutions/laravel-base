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
     * Main Scope
     * @var string|null
     */
    protected ?string $scope;

    /**
     * Main Endpoint
     * @var string|null
     */
    protected ?string $endpoint;

    /**
     * @var HttpService|null
     */
    protected ?HttpService $httpService;

    protected function custom_construct(){

    }

    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            $this->custom_construct();

            $this->httpService = new HttpService($request, $this->endpoint);

            return $next($request);
        });
    }

    ##############################
    # Main interfaces
    ##############################
    /**
     * Main get all interface
     * @return JsonResponse|string
     */
    public function all(): JsonResponse|string
    {
        $response = $this->httpService->requestHttp->get($this->scope . '/all');

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }

    public function remoteSearch(string $query): JsonResponse|string
    {
        $response = $this->httpService->requestHttp->get( $this->scope . '/remote-search/' . $query );

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }


    public function findBy(Request $request): JsonResponse|string
    {
        $response = $this->httpService->requestHttp->get( $this->scope . '/findBy/', $request->query->all() );

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }

    /**
     * Main paginated list interface
     * @param Request $request
     * @return JsonResponse|string
     */
    public function list(Request $request): JsonResponse|string
    {
        $response = $this->httpService->requestHttp->get($this->scope, $request->query->all());
        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }

    /**
     * Main read interface
     * @param $id
     * @return JsonResponse|string
     */
    public function read($id): JsonResponse|string
    {
        $response = $this->httpService->requestHttp->get($this->scope . '/' . $id);

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }

    /**
     * Main create interface
     * @param Request $request
     * @return JsonResponse|string
     */
    public function create(Request $request): JsonResponse|string
    {
        $response = $this->httpService->requestHttp->post($this->scope, $request);

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }

    /**
     * Main update interface
     * @param Request $request
     * @return JsonResponse|string
     */
    public function update(Request $request): JsonResponse|string
    {
        $response = $this->httpService->requestHttp->put($this->scope, $request);

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }


    /**
     * Main destroy interface
     * @param Request $request
     * @return JsonResponse|string
     */
    public function destroy(Request $request): JsonResponse|string
    {
        $response = $this->httpService->requestHttp->delete($this->scope, $request);

        return $this->httpService->jsonResponse(
            $response,
            $response->status()
        );
    }
}
