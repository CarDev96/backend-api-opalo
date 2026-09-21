<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuSection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_section_id')->orderBy('order_index');
    }
}
