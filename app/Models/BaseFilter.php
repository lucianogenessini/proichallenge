<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;


class BaseFilter
{
    /**
     * @param  int|null  $perPage
     * @param  array|null  $orderBy
     * @param  array|null  $filters
     * @param  array|null  $select
     * @param  array|null  $withRelations
     * @param  Builder|null  $builder
     * @param  string|null  $groupBy
     * @param  int|null $page
     */
    public function __construct(
        private ?int $perPage = null,
        private ?array $orderBy = null,
        private ?array $filters = null,
        private ?array $select = null,
        private ?array $withRelations = null,
        private ?Builder $builder = null,
        private ?string $groupBy = null,
        private ?int $page = null,
    ) {
    }

    /**
     * Create a new instance of the BaseFilter object.
     *
     * @return BaseFilter
     */
    public static function create(): BaseFilter
    {
        return new BaseFilter();
    }

    /**
     * Get per page.
     *
     * @return int|null
     */
    public function getPerPage(): ?int
    {
        return $this->perPage;
    }

    /**
     * Set per page.
     *
     * @param  int|null  $perPage
     * @return BaseFilter
     */
    public function setPage(?int $page): BaseFilter
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get per page.
     *
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * Set per page.
     *
     * @param  int|null  $perPage
     * @return BaseFilter
     */
    public function setPerPage(?int $perPage): BaseFilter
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Get order by.
     *
     * @return array|null
     */
    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

    /**
     * Set order by.
     *
     * @param  array|null  $orderBy
     * @return BaseFilter
     */
    public function setOrderBy(?array $orderBy): BaseFilter
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * Get filters.
     *
     * @return array|null
     */
    public function getFilters(): ?array
    {
        return $this->filters;
    }

    /**
     * Set filters.
     *
     * @param  array|null  $filters
     * @return BaseFilter
     */
    public function setFilters(?array $filters): BaseFilter
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get columns to select.
     *
     * @return array|null
     */
    public function getSelect(): ?array
    {
        return $this->select;
    }

    /**
     * Set columns to select.
     *
     * @param  array|null  $select
     * @return BaseFilter
     */
    public function setSelect(?array $select): BaseFilter
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Get relations to load.
     *
     * @return array|null
     */
    public function getWithRelations(): ?array
    {
        return $this->withRelations;
    }

    /**
     * Set relations to load.
     *
     * @param  array|null  $withRelations
     * @return BaseFilter
     */
    public function setWithRelations(?array $withRelations): BaseFilter
    {
        $this->withRelations = $withRelations;

        return $this;
    }

    /**
     * Get query context.
     *
     * @return Builder|null
     */
    public function getBuilder(): ?Builder
    {
        return $this->builder;
    }

    /**
     * Set query context.
     *
     * @param  Builder|null  $builder
     * @return BaseFilter
     */
    public function setBuilder(?Builder $builder): BaseFilter
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Get grouping.
     *
     * @return string|null
     */
    public function getGroupBy(): ?string
    {
        return $this->groupBy;
    }

    /**
     * Set grouping.
     *
     * @param  string|null  $groupBy
     * @return BaseFilter
     */
    public function setGroupBy(?string $groupBy): BaseFilter
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * Bind the params of the listing.
     *
     * @param  Request  $request
     * @param  Builder|null  $builder
     * @return BaseFilter
     */
    public static function fromRequest(Request $request, Builder $builder = null): BaseFilter
    {
        $perPage = $request->perPage;
        $filters = $request->filters;
        $orderBy = $request->orderBy;
        $select = $request->select;
        $relation = $request->withRelations;
        $groupBy = $request->group;
        $page = $request->page;

        return BaseFilter::create()
            ->setPerPage($perPage)
            ->setFilters($filters)
            ->setOrderBy($orderBy)
            ->setWithRelations($relation)
            ->setSelect($select)
            ->setGroupBy($groupBy)
            ->setBuilder($builder)
            ->setPage($page);
    }
}
