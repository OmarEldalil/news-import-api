<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $title
 * @property string $content
 * @property string|null $url
 * @property string $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|News whereUrl($value)
 * @mixin \Eloquent
 */
class News extends Model
{
    use HasUlids;

    protected $table = "news";

    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'content',
        'url',
    ];

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_news', 'news_id', 'category_id');
    }
}
