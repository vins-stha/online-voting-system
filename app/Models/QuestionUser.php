<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionUser extends Model
{
    use HasFactory;
    protected $table = "question_user";

    public function voters()
    {
        return $this->belongsTo(User::class)->wherePivot('type', 'voters');
    }

    public function voted_questions()
    {
        return $this->belongsTo(Question::class)->wherePivot('type', 'voted_questions');
    }
}