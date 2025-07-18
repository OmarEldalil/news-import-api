<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\News> $news
 * @property-read int|null $news_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
    // used ULID to bulk insert categories and not wait for the auto-incrementing ID from the database
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'categories';

    public function news(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(News::class, 'category_news', 'category_id', 'news_id');
    }
}
