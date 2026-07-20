<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BibleNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'livro',
        'capitulo',
        'versiculo_inicial',
        'versiculo_final',
        'conteudo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
