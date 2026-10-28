<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EstudianteMatriculado extends Pivot
{
    /** @use HasFactory<\Database\Factories\EstudianteMatriculadoFactory> */
    use HasFactory;

    protected $table = 'estudiante_matriculados';

    public $incrementing = true;

    protected function casts(): array
    {
        return ['nota' => 'float'];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class);
    }
}
