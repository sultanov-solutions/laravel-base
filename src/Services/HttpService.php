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

    private array $extendHeaders = [];

    public function __construct(Request $request, string $endpoint)
    {
        if ($endpoint && !empty($endpoint))
            $this->setBaseUrl($endpoint);

        $this->requestHttp = Http::withToken($request->bearerToken())
            ->baseUrl($this->getBaseUrl())
            ->withHeaders($this->setHeaders($request))
            ->acceptJson();
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

    public function jsonResponse(Response $response, $fail_status = 404, $message = 'Page not found'): JsonResponse|string
    {
        $responseType = 'json';

        $responseHeaders = $response->headers();
        if (isset($responseHeaders['Content-Type']) && collect($responseHeaders['Content-Type'])->filter(fn($ct) => str($ct)->contains('text/html'))->count())
            $responseType = 'html';

        if ($responseType == 'html')
            return response($response->body(), $response->status());

        return response()->json($response->json(), $fail_status);
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

    public function extendHeaders(array $data = []): static
    {
        $this->extendHeaders = $data;
        return $this;
    }

    private function setHeaders(Request $request): array
    {
        $headers = [];

        if ($request->hasHeader('X-PRJ-META-USER-ID'))
            $request->headers->remove('X-PRJ-META-USER-ID');

        if ($request->hasHeader('X-PRJ-META-ORG-ID'))
            $request->headers->remove('X-PRJ-META-ORG-ID');


        if ($request->hasHeader('X-PRJ-META-EXT'))
            $request->headers->remove('X-PRJ-META-EXT');

        if ($user = $request->user()) {
            if (!empty($user['id']) && is_numeric($user['id']))
                $headers['X-PRJ-META-USER-ID'] = $user['id'];

            if (!empty($user['organization_id']) && is_numeric($user['organization_id']))
                $headers['X-PRJ-META-ORG-ID'] = $user['organization_id'];

            $extendHeaders = $this->extendHeaders;
            if (is_array($extendHeaders) && count($extendHeaders))
                $headers['X-PRJ-META-EXT'] = implode(',', $extendHeaders);
        }

        return $headers;
    }
}
