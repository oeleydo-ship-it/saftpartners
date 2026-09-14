<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'slug', 'position', 'biography', 'photo', 'email', 'linkedin_url', 'sort_order', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
