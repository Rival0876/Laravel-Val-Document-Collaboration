<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['title', 'user_id', 'yjs_state'];

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}