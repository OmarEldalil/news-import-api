<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $news_id
 * @property int $category_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NewsCategory whereNewsId($value)
 * @mixin \Eloquent
 */
class NewsCategory extends Model
{
    protected $table = "category_news";

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'news_id',
        'category_id',
    ];
}
