<?php

namespace App\Models;

use App\Enums\Role;
use App\Notifications\ArticleOutOfStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends AbstractBaseModel
{
    protected $fillable = [
        'name',
        'stock',
        'category_id',
        'price_unit',
    ];

    protected $casts = [
        'price_unit' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $search = isset($filters['search']) ? $filters['search'] : null;
        
        return $query
            ->when(!is_null($search) && strlen($search) >= 3, function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                ->orWhereHas('category', function ($query) use ($filters){
                    $query->where('name', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when(!empty($filters['categories']), function ($query) use ($filters) {
                $query->whereIn('category_id', $filters['categories']);
            });
    }

    
    /**
     * Check if the stock is equal to 0 and send notification to admin users.
     * The notification will be queued.
     * 
     * @return void
     */
    public function resolveStockQuantity(): void
    {
        if ($this->stock == 0 ) {
            $admin_users = User::whereHas('roles', function($query){
                $query->where('name', Role::SUPER_ADMIN_ROLE->value);
            })->get();

            $admin_users->each(function ($user){
                $user->notify(new ArticleOutOfStock($this));
            });
        }
    }
}
