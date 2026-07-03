<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileShareItem extends Model
{
    protected $fillable = [
        'file_share_id',
        'filename',
        'file_path',
        'file_size',
        'mime_type',
    ];

    public function fileShare()
    {
        return $this->belongsTo(FileShare::class);
    }
}
