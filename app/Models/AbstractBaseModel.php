<?php

namespace App\Models;

use App\Exceptions\BaseFilterException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use Yajra\Auditable\AuditableWithDeletesTrait;


abstract class AbstractBaseModel extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes, AuditableWithDeletesTrait, HasFactory;

    protected $hidden = [
        'deleted_at',
        'created_by',
        'updated_by',
        'created_by',
        'deleted_by',
    ];
    
    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            
            if (Auth::user()) {
                $user = Auth::user();
                $model->created_by = $user->id;
                $model->updated_by = $user->id;
            }
        });

        static::updating(function ($model) {
            $user = Auth::user();
            $model->updated_by = $user->id;
        });
    }

    /**
     * Get model listing.
     * Returns the model paginated and apply filters, order, etc
     *
     * @param  BaseFilter  $filters
     * @return LengthAwarePaginator
     *
     */
    public static function listFromParams(BaseFilter $filters): LengthAwarePaginator
    {
        return self::list($filters->getPerPage(), $filters->getOrderBy(), $filters->getFilters(), $filters->getSelect(),
            $filters->getWithRelations(), $filters->getBuilder(), $filters->getGroupBy(), $filters->getPage());
    }

    /**
     * Adds filterable, sortable and pagination capabilities to all models that inherit from AbtractModels.
     *
     * @param  int|null  $perPage
     * @param  array|null  $orderBy
     * @param  array|null  $filters
     * @param  array|null  $select
     * @param  array|null  $withRelation
     * @param  Builder|null  $builder
     * @param  string|null  $groupBy
     * @param  int|null  $page
     * @return LengthAwarePaginator
     *
     */
    public static function list(
        int $perPage = null,
        array $orderBy = null,
        array $filters = null,
        array $select = null,
        array $withRelation = null,
        Builder $builder = null,
        string $groupBy = null,
        int|null $page = 1,
    ): LengthAwarePaginator {
        $orderBy = $orderBy ?? ['key' => 'id', 'order' => 'DESC'];
        $perPage = $perPage ?? 10;

        $query = $builder !== null ? $builder : self::query();

        $model = $query->getModel();

        if (! is_null($select)) {
            $tableColumns = DB::getSchemaBuilder()->getColumnListing($model->getTable());
            $requestedColumns = [];
            $tableColumns = array_map(fn ($item) => str_replace('`', '', "$item"), $tableColumns);

            foreach ($select as $column) {
                $column = $column;
                if (in_array($column, $tableColumns)) {
                    $requestedColumns[] = $model->getTable().'.'.$column;
                }
            }
            $requestedColumns = empty($requestedColumns) ? [$model->getTable().'.*'] : $requestedColumns;
            $query = $query->select($requestedColumns);

        } else {
            $query = $query->select($model->getTable().'.*');
        }
        if (is_iterable($filters)) {
            $query = self::applyFilters($model, $filters, $query);
        }

        if (is_iterable($withRelation)) {
            foreach ($withRelation as $relation) {
                if ($model->isRelation($relation['name'])) {
                    $relationString = array_map(fn ($item) => $item, $relation['columns']);
                    $relationString = implode(',', $relationString);
                    $query = $query->with([$relation['name'].":$relationString"]);
                }
            }
        }

        if (is_null($groupBy)) {
            $query = self::applyOrder($orderBy['key'], $query, $model, $orderBy['order']);
        } else {
            $query = self::applyOrder($groupBy, $query, $model);
        }
        return $query->paginate($perPage, null, null, $page);
    }

    /**
     * Apply the filters.
     *
     * @param  Builder|Model  $model
     * @param  array  $filters
     * @param  Builder  $query
     * @return Builder
     *
     */
    public static function applyFilters(Builder|Model $model, array $filters, Builder $query): Builder
    {
        foreach ($filters as $condition) {

            if ($condition['operator'] == 'like') {
                $condition['value'] = '%'.$condition['value'].'%';
            }

            $value = match ($condition['value']) {
                'startOfDay'   => now()->startOfDay()->toDateTimeString(),
                'endOfDay'     => now()->endOfDay()->toDateTimeString(),
                'startOfMonth' => now()->startOfMonth()->toDateTimeString(),
                'endOfMonth'   => now()->endOfMonth()->toDateTimeString(),
                default        => $condition['value'] === 'null' ? null : $condition['value'],
            };

            $explodedKey = explode('.', $condition['key']);
            $queryType = match ($condition['type'] ?? 'AND') {
                'OR'    => 'orWhere',
                default => 'where',
            };

            if (count($explodedKey) > 1) {
                $relationName = $explodedKey[0];
                $relationItem = $explodedKey[1];

                if ($model instanceof Builder || $model->isRelation($relationName)) {
                    $tableName = '';

                    if ($model instanceof Model && $model->isRelation($relationName)) {
                        $tableName = $model->$relationName()->getRelated()->getTable();
                        $tableName .= '.';
                    }

                    if ($model instanceof Builder) {
                        $tableName = $model->getModel()->$relationName()->getRelated()->getTable();
                        $tableName .= '.';
                    }

                    $query = match ($operator = $condition['operator']) {
                        'like',
                        'ilike',
                        '=',
                        '!=',
                        '>',
                        '<',
                        '>=',
                        'isNull',
                        'isNotNull',
                        '<='        => self::resolveCommonOperator(

                            $query, $relationName, $relationItem, $operator, $value, $queryType, $tableName),

                        'hasEquals' => self::resolveHasOperator($query, $relationName, $relationItem, $operator,
                            $value, $queryType)
                    };
                } else {
                    throw new BaseFilterException("The $relationName does not exists.");
                }
            } else {
                if (in_array($condition['operator'], ['isNull', 'isNotNull'])) {
                    if ($condition['operator'] == 'isNull') {
                        $queryType .= 'Null';
                    } else {
                        $queryType .= 'NotNull';
                    }
                    $query = $query->$queryType($condition['key']);
                } else {

                    $query = $query->$queryType($condition['key'], $condition['operator'],
                        $value);
                }
            }
        }

        return $query;
    }

    /**
     * Resolves the common filters from a relation.
     *
     * @param  Builder  $query
     * @param  string  $relationName
     * @param  string  $relationItem
     * @param  string  $operator
     * @param  string|int|array|null  $value
     * @param  string  $queryType
     * @return Builder
     */
    private static function resolveCommonOperator(
        Builder $query,
        string $relationName,
        string $relationItem,
        string $operator,
        string|int|array|null $value,
        string $queryType,
        string|null $tableName
    ): Builder {
        $queryType = $queryType.'Has';

        return $query->$queryType(
            $relationName,
            function ($query) use ($tableName, $relationItem, $operator, $value) {
                switch ($operator) {

                    case 'isNull':
                        $query->whereNull($tableName.$relationItem);
                        break;

                    case 'isNotNull':
                        $query->whereNotNull($tableName.$relationItem);
                        break;
                    default:
                        $query->where($tableName.$relationItem, $operator, $value);
                        break;

                }
            }
        );
    }

    /**
     * Resolve the has operator in the filters that uses a relation.
     *
     * @param  Builder  $query
     * @param  string  $relationName
     * @param  string  $relationItem
     * @param  string  $operator
     * @param  string|int|array  $value
     * @param  string  $queryType
     * @return Builder
     */
    private static function resolveHasOperator(
        Builder $query,
        string $relationName,
        string $relationItem,
        string $operator,
        string|int|array $value,
        string $queryType
    ): Builder {
        $queryType = $queryType.'Has';

        return $query->with($relationName)->$queryType($relationName,

            function (Builder $builder) use ($relationItem, $value, $operator) {
                $builder->where($relationItem, $operator, $value);
            });
    }

    /**
     * Order the given query by the given key.
     *
     * The key must be in the following formats:
     * - relation.fieldToUseToOrder
     * - propertyOfTheModel
     *
     * Example:
     * - contactChannels.channel
     * - firstName
     *
     * @param  string  $key
     * @param  Builder  $query
     * @param  Model  $model
     * @param  string  $orderDirection
     * @return Builder
     */
    private static function applyOrder(string $key, Builder $query, Model $model, string $orderDirection = 'ASC'): Builder
    {
        $explodedOrderField = explode('.', $key);
        //Here we assume that the key is a property of the model.
        if (count($explodedOrderField) === 1) {
            $field = $explodedOrderField[0];
            $field = $model->getTable().'.'.$field;
            $query = $query->orderBy($field, $orderDirection);
        }
        //Here we assume that the key is from a relation: relation.fieldName
        if (count($explodedOrderField) === 2) {
            $relationName = $explodedOrderField[0];
            $relationField = $explodedOrderField[1];
            $relationType = $query->getRelation($relationName);
            $related = $query->getRelation($relationName)->getRelated();
            $query = match ($relationType::class) {
                BelongsTo::class => self::processOrderOfBelongsTo($query, $model, $related, $relationType, $relationField, $orderDirection),
                BelongsToMany::class => self::processOrderOfBelongsToMany($query, $model, $relationType, $relationField, $orderDirection),
                HasMany::class =>self::processOrderOfHasManyRelation($query, $model, $relationType, $relationField, $orderDirection),
                default => $query
            };
        }

        return $query;
    }

    /**
     * Process the order of a belongs to relation.
     *
     * @param  Builder  $query
     * @param  Model  $model
     * @param  Model  $related
     * @param  Relation  $relationType
     * @param  string  $relationField
     * @param  string  $orderDirection
     * @return Builder
     */
    private static function processOrderOfBelongsTo(Builder $query, Model $model, Model $related, Relation $relationType, string $relationField, string $orderDirection = 'ASC'): Builder
    {
        $primaryMatch = $model->getTable().'.'.$relationType->getRelated()->getForeignKey();
        $relationMatch = $related->getTable().'.'.$related->getKeyName();
        $orderByField = $related->getTable().'.'.$relationField;

        return $query->join($related->getTable(), $primaryMatch, '=', $relationMatch)->orderBy($orderByField, $orderDirection);
    }

    /**
     * Process the order of a belongs to many relation.
     *
     * @param  Builder  $query
     * @param  Model  $model
     * @param  Relation  $relationType
     * @param  string  $relationField
     * @param  string  $orderDirection
     * @return Builder
     */
    public static function processOrderOfBelongsToMany(Builder $query, Model $model, Relation $relationType, string $relationField, string $orderDirection = 'ASC'): Builder
    {
            $query = $query->join(
                $relationType->getTable(), $relationType->getTable().'.'.$relationType->getForeignPivotKeyName(),
                '=',
                $model->getTable().'.'.$relationType->getRelated()->getKeyName())
                ->join(
                    $relationType->getRelated()->getTable(),
                    $relationType->getTable().'.'.$relationType->getRelatedPivotKeyName(),
                    '=',
                    $relationType->getRelated()->getTable().'.'.$relationType->getRelated()->getKeyName())
                ->orderBy($relationType->getRelated()->getTable().'.'.$relationField, $orderDirection);

        return $query;
    }

    /**
     * Process the order of a has many relation.
     *
     * @param  Builder  $query
     * @param  Model  $model
     * @param  Relation  $relationType
     * @param  string  $relationField
     * @param  string  $orderDirection
     * @return Builder
     */
    public static function processOrderOfHasManyRelation(Builder $query, Model $model, Relation $relationType, string $relationField, string $orderDirection = 'ASC'): Builder
    {
        return $query->join(
            $relationType->getRelated()->getTable(),
            $model->getTable().'.'.$model->getKeyName(),
            '=',
            $relationType->getRelated()->getTable().'.'.$model->getForeignKey())
            ->orderBy($relationType->getRelated()->getTable().'.'.$relationField, $orderDirection);
    }
}
