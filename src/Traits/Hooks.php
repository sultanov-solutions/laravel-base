<?php

namespace SultanovSolutions\LaravelBase\Traits;

use Illuminate\Database\Eloquent\Builder;
use SultanovSolutions\LaravelBase\Models\BaseModel as Model;
use Illuminate\Http\Request;

trait Hooks
{
    ##############################
    # List hooks
    ##############################
    /**
     * Inject request modifications to LISTs interfaces
     */
    protected function beforeListHook(Request $request): Request
    {
        return $request;
    }

    /**
     * Inject Builder Modifications to LISTs interfaces
     */
    protected function afterListHook(Builder $builder, Request $injectedRequest, Request $request): Builder
    {
        return $builder;
    }

    ##############################
    # Create hooks
    ##############################
    /**
     * Inject request modifications to CREATE interface
     */
    protected function beforeCreateHook(Request $request): Request
    {
        return $request;
    }

    /**
     * After create item hook
     */
    protected function afterCreateHook(Model $item, array $data): Model
    {
        return $item;
    }

    ##############################
    # Read hooks
    ##############################
    /**
     * Inject request modifications to READ interface
     */
    protected function beforeReadHook(Request $request): Request
    {
        return $request;
    }

    /**
     * After READ item hook
     */
    protected function afterReadHook(Model $item): Model
    {
        return $item;
    }

    ##############################
    # Update hooks
    ##############################
    /**
     * Inject request modifications to UPDATE interface
     */
    protected function beforeUpdateHook(Request $request): Request
    {
        return $request;
    }

    /**
     * After item Update hook
     */
    protected function afterUpdateHook(Model $item, array $data): Model
    {
        return $item;
    }

    ##############################
    # Destroy hooks
    ##############################
    /**
     * Inject request modifications to DESTROY interface
     */
    protected function beforeDestroyHook(Request $request): Request
    {
        return $request;
    }

    /**
     * After item Destroyed hook
     */
    protected function afterDestroyHook(Model $item, array $data): void
    {
    }
}
