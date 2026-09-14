<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Market extends Model
{
    use SoftDeletes;
    protected $fillable = ['title', 'slug', 'description', 'icon', 'image', 'sort_order', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
