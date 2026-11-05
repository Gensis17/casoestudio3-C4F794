<?php

namespace App\Models;

use Database\Factories\DocenteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    /** @use HasFactory<DocenteFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cod_empleado',
        'nombre_empleado',
        'apellidos_empleado',
    ];

    public function getCodEmpleadoAttribute(): ?int
    {
        return $this->attributes['cod_empleado'] ?? null;
    }

    public function setCodEmpleadoAttribute(?int $value): void
    {
        $this->attributes['cod_empleado'] = $value;
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class)->withTimestamps();
    }

    // Estudiantes a los que aconseja
    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'consejero_id');
    }
}
