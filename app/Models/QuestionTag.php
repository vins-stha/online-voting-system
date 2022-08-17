<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTag extends Model
{
    use HasFactory;
    protected $table = "question_tag";

    public function questions()
    {
        return $this->belongsTo(Question::class);
    }

    public function tags()
    {
        return $this->belongsTo(Tag::class);
    }
}
