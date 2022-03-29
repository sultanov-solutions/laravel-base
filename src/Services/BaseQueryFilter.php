<?php
namespace SultanovSolutions\LaravelBase\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
// phpcs:disable Generic.ControlStructures.InlineControlStructure

class BaseQueryFilter
{
    public Builder $builder;

    public function __construct(private Request $request, private Model $model, private string $scope)
    {
    }

    public function updateRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }

    public function listFilter(): Builder
    {
        return $this->orderByQuery()->filterQuery()->searchQuery()->builder;
    }

    public function validateRequest(string $method = null): array|MessageBag
    {
        $data = [];

        $requestOnlyFields = config('requests.' . $this->scope . '.default');

        if (config('requests.' . $this->scope . '.' . $method))
            $requestOnlyFields = array_merge($requestOnlyFields, config('requests.' . $this->scope . '.' . $method));

        if ($requestOnlyFields && count($requestOnlyFields))
            $data = $this->request->only(collect($requestOnlyFields)->keys()->toArray());

        $validation = Validator::make($data, $requestOnlyFields);

        if ($validation->fails()) {
            return $validation->errors();
        }

        return $data;
    }

    private function orderByQuery(): static
    {
        if ($this->request->has('orderDir') && $this->request->has('orderBy'))
            $this->builder = $this->model->orderBy($this->request->get('orderBy'), $this->request->get('orderDir'));

        return $this;
    }

    private function filterQuery(): static
    {
        if ($this->request->has('filter')) {
            $filterData = $this->request->filter;

            foreach ($filterData as $col => $filterValue) {
                if (Schema::hasColumn($this->model->getTable(), $col)) {
                    if (!is_array($filterValue)) {
                        $this->builder = $this->builder->orWhere($col, 'like', '%' . $filterValue . '%');
                    } else {
                        $this->builder = $this->builder->orWhereIn($col, $filterValue);
                    }
                }
            }
        }

        return $this;
    }

    private function searchQuery(): static
    {
        if ($this->request->has('s')) {
            foreach (collect($this->model->getFillable())->filter(fn($i) => $i !== 'password')->toArray() as $field)
                $this->builder = $this->builder->orWhere($field, 'like', '%' . htmlspecialchars(trim($this->request->s)) . '%');
        }

        return $this;
    }
}
