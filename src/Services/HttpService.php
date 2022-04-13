<?php

namespace SultanovSolutions\LaravelBase\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HttpService
{
    public ?PendingRequest $requestHttp;

    private string $baseUrl = '/api';

    public function __construct(Request $request, string $endpoint)
    {
        if ($endpoint && !empty($endpoint))
            $this->setBaseUrl($endpoint);

        $this->requestHttp = Http::withToken($request->bearerToken())->baseUrl( $this->getBaseUrl() );
    }

    public function hydratePaginationResponse(Response $response): JsonResponse
    {
        if ($response->status() === 200) {
            $pagination = $response->json();

            $pagination['first_page_url'] = (string)str($pagination['first_page_url'])->replace(env('SS_OAUTH_URL'), env('APP_URL'));
            $pagination['last_page_url'] = (string)str($pagination['last_page_url'])->replace(env('SS_OAUTH_URL'), env('APP_URL'));
            $pagination['next_page_url'] = (string)str($pagination['next_page_url'])->replace(env('SS_OAUTH_URL'), env('APP_URL'));
            $pagination['prev_page_url'] = (string)str($pagination['prev_page_url'])->replace(env('SS_OAUTH_URL'), env('APP_URL'));
            $pagination['path'] = (string)str($pagination['path'])->replace(env('SS_OAUTH_URL'), env('APP_URL'));
            unset($pagination['links']);

            return response()->json($pagination);
        }

        return $this->jsonResponse($response, $response->status());
    }

    public function jsonResponse(Response $response, $fail_status = 404, $message = 'Page not found'): JsonResponse
    {
        if ($response->status() === 200)
            return response()->json($response->json());

        return response()->json($response->body(), $fail_status);
    }

    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
