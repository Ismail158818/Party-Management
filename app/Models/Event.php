<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'date', 'location','tickect_price','tickets_limit', 'media', 'description'];    
    public function users()
    {
        return $this->belongsToMany(User::class,'event_users');
    }
    public function tickets() 
    {
        return $this->hasMany(Ticket::class);
    }
    public function comments() 
    {
        return $this->hasMany(Comment::class);
    }
}
