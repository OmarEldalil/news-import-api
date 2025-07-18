<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $file_path
 * @property string $status
 * @property string $created_at
 * @property string|null $error_report_path
 * @property string|null $processed_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest whereErrorReportPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImportRequest whereStatus($value)
 * @mixin \Eloquent
 */
class ImportRequest extends Model
{
    // TODO: store original file name for readability
    public $timestamps = false;

    protected $hidden = ['file_path'];

    protected function errorReportPath(): Attribute
    {
        return Attribute::make(
            get: fn(string|null $value) => $value ? Storage::url($value) : null,
        );
    }
}
