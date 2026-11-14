<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveiFoto extends Model
{
    use SoftDeletes;

    protected $table = 'survei_foto';

    protected $fillable = [
        'survei_komponen_id',
        'path_file',
        'mime_type',
        'ukuran_byte',
        'nama_asli',
    ];

    protected $casts = [
        'ukuran_byte' => 'integer',
    ];

    public function surveiKomponen(): BelongsTo
    {
        return $this->belongsTo(SurveiKomponen::class, 'survei_komponen_id');
    }
}
