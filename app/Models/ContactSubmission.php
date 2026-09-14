<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactSubmission extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'email', 'phone', 'company', 'subject', 'message', 'status', 'ip_address', 'user_agent'];
}
