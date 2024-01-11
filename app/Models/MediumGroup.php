<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediumGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'details',
        'major_group_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function major_group()
    {
        return $this->belongsTo(MajorGroup::class);
    }

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }
}
