<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresFoto extends Model
{
    protected $table = 'progres_foto';

    protected $fillable = [
        'progres_renovasi_id',
        'path_file',
    ];

    public function progres(): BelongsTo
    {
        return $this->belongsTo(ProgresRenovasi::class, 'progres_renovasi_id');
    }
}
