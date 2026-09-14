<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public static function values(): array
    {
        // Defaults fill any key missing from the database so pages never render empty.
        return array_merge(\App\Support\DefaultContent::settings(), static::query()->pluck('value', 'key')->all());
    }
}
