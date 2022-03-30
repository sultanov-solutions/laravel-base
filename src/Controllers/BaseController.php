<?php

namespace SultanovSolutions\LaravelBase\Controllers;

use SultanovSolutions\LaravelBase\Services\BaseQueryFilter;
use SultanovSolutions\LaravelBase\Traits\Hooks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelBaseController;

abstract class BaseController extends LaravelBaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, Hooks;

    protected BaseQueryFilter $queryFilter;

    protected string $scope;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->queryFilter = new BaseQueryFilter($request, new $this->model, $this->scope);
            return $next($request);
        });
    }

    /**
     * Default perPage
     * @var int
     */
    protected int $perPage = 15;

    /**
     * Default class model
     * @var string|null
     */
    protected mixed $model;

    /**
     * Default get paginated list interface
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        // Inject request modifications
        $injectedRequest = $this->beforeListHook($request);

        // Apply base list filters
        $builder = $this->queryFilter->updateRequest($injectedRequest)->listFilter();

        // Inject Builder Modifications
        $builder = $this->afterListHook($builder, $injectedRequest, $request);

        return response()->json(
            $builder->paginate($request->perPage ?? $this->perPage)
        );
    }

    /**
     * Default Get All interface
     * @return JsonResponse
     */
    public function all(): JsonResponse
    {
        return response()->json($this->model::all()->pluck('name', 'id'));
    }

    /**
     * Default Create item interface
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        $item = null;

        // Inject request modifications
        $request = $this->beforeCreateHook($request);

        // Validate Request Data
        $data = $this->queryFilter->updateRequest($request)->validateRequest('create');

        if ($data instanceof MessageBag) {
            event('create-error.' . $this->scope, [$data]);

            return response()->json($data, ResponseAlias::HTTP_BAD_REQUEST);
        }

        event('creating.' . $this->scope, [$data]);

        try {
            $item = $this->model::create($data);
        } catch (\Exception $exception) {
            if (str($exception->getMessage())->contains('Duplicate entry'))
                return response()->json(['status' => false, 'message' => 'Duplicate entry'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!$item) {
            event('item-not-created.' . $this->scope, [$data]);

            return response()->json(['status' => false], ResponseAlias::HTTP_BAD_REQUEST);
        }

        // Apply updates after item created(Created relations, files, etc..)
        $item = $this->afterCreateHook($item, $data);

        event('created.' . $this->scope, [$item, $data]);

        return response()->json($item);
    }

    /**
     * Default Get Single item interface
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function read(Request $request, $id): JsonResponse
    {
        // Inject request modifications
        $request = $this->beforeReadHook($request);

        $item = $this->model::find($id);

        if (!$item) {
            event('item-not-found.' . $this->scope, [$id]);

            return response()->json('Not found', ResponseAlias::HTTP_NOT_FOUND);
        }

        $item = $this->afterReadHook($item);

        return response()->json($item);
    }

    /**
     * Default Update Single item interface
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        // Inject request modifications
        $request = $this->beforeUpdateHook($request);

        // Validate Request Data
        $data = $this->queryFilter->updateRequest($request)->validateRequest('update');

        if ($data instanceof MessageBag) {
            event('update-error.' . $this->scope, [$data]);

            return response()->json($data, ResponseAlias::HTTP_BAD_REQUEST);
        }

        $item = $this->model::find($data['id']);

        if (!$item) {
            event('item-not-updated.' . $this->scope, [$data]);
            return response()->json('Not found', ResponseAlias::HTTP_NOT_FOUND);
        }

        event('updating.' . $this->scope, [$item, $data]);

        $item->update($data);

        // Apply updates after item updated(Update relations, files, etc..)
        $item = $this->afterUpdateHook($item, $data);

        event('updated.' . $this->scope, [$item, $data]);

        return response()->json($item);
    }

    /**
     * Default item destroy interface
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        // Inject request modifications
        $request = $this->beforeDestroyHook($request);

        // Validate Request Data
        $data = $this->queryFilter->updateRequest($request)->validateRequest('destroy');

        if ($data instanceof MessageBag) {
            event('destroy-error.' . $this->scope, [$data]);
            return response()->json($data, ResponseAlias::HTTP_BAD_REQUEST);
        }

        $item = $this->model::find($data['id']);

        if (!$item) {
            event('item-not-destroyed.' . $this->scope, [$data]);

            return response()->json('Not found', ResponseAlias::HTTP_NOT_FOUND);
        }

        event('destroying.' . $this->scope, [$item, $data]);

        $item->delete();

        // Apply updates after item destroyed(Remove relations, clear files, etc..)
        $this->afterDestroyHook($item, $data);

        event('destroyed.' . $this->scope, [$item, $data]);

        return response()->json($item);
    }
}
