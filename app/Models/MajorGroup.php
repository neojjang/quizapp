<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MajorGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mediumGroups()
    {
        return $this->hasMany(MediumGroup::class);
    }
}
