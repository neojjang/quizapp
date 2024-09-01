<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    // protected $fillable = [
    //     'name',
    //     'description',
    //     'is_active',
    //     'details'
    // ];
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class_room()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function quizHeaders()
    {
        return $this->hasMany(QuizHeader::class);
    }

    public function sectionFiles()
    {
        return $this->hasMany(SectionFiles::class);
    }
}
