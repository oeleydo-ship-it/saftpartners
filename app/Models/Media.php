<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    protected $fillable = ['user_id', 'disk', 'path', 'original_name', 'mime_type', 'size', 'alt_text'];
    protected $appends = ['url'];
    public function getUrlAttribute(): string { return \Storage::disk($this->disk)->url($this->path); }
}
