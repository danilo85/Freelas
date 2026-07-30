<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditorialRevisionComment extends Model
{
    protected $fillable = [
        'editorial_revision_correction_id',
        'user_id',
        'author_name',
        'message',
    ];

    public function correction()
    {
        return $this->belongsTo(EditorialRevisionCorrection::class, 'editorial_revision_correction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
