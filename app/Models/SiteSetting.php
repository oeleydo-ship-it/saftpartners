<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    public static function values(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }
}
