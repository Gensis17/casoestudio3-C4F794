<?php

namespace App\Models;

use Database\Factories\GrupoFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    /** @use HasFactory<GrupoFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'curso_id',
        'numero_ciclo',
        'anio',
        'ano',
        'numero_grupo',
        'abierto',
    ];

    protected function casts(): array
    {
        return [
            'abierto' => 'boolean',
            'numero_ciclo' => 'integer',
            'anio' => 'integer',
            'numero_grupo' => 'integer',
        ];
    }

    public function getAnoAttribute(): ?int
    {
        return $this->anio;
    }

    public function setAnoAttribute(?int $value): void
    {
        $this->attributes['anio'] = $value;
    }

    public function getNumeroCicloAttribute(): ?int
    {
        return isset($this->attributes['numero_ciclo']) ? (int) $this->attributes['numero_ciclo'] : null;
    }

    public function setNumeroCicloAttribute(?int $value): void
    {
        $this->attributes['numero_ciclo'] = $value;
    }

    public function getNumeroGrupoAttribute(): ?int
    {
        return isset($this->attributes['numero_grupo']) ? (int) $this->attributes['numero_grupo'] : null;
    }

    public function setNumeroGrupoAttribute(?int $value): void
    {
        $this->attributes['numero_grupo'] = $value;
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(Docente::class)->withTimestamps();
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_matriculados')
            ->using(EstudianteMatriculado::class)
            ->withPivot('nota')
            ->withTimestamps();
    }

    /**
     * Asigna un docente al grupo verificando el límite de 1 a 2 docentes (RN-02).
     *
     * @throws DomainException
     */
    public function asignarDocente(Docente|int $docente): void
    {
        if ($this->docentes()->count() >= 2) {
            throw new DomainException('Un grupo no puede tener más de dos docentes.');
        }

        $docenteModel = is_int($docente)
            ? Docente::where('cod_empleado', $docente)->firstOrFail()
            : $docente;

        $this->docentes()->attach($docenteModel);
    }

    /**
     * Consulta si el grupo cumple los requisitos para abrirse (RN-02, RN-05).
     */
    public function puedeAbrirse(): bool
    {
        $tieneDocente = $this->docentes()->count() >= 1;
        $tieneMinimoEstudiantes = $this->estudiantes()->count() >= 15;

        return $tieneDocente && $tieneMinimoEstudiantes;
    }

    /**
     * Abre el grupo si cumple los requisitos; de lo contrario lanza una excepción (RN-05).
     *
     * @throws DomainException
     */
    public function abrir(): void
    {
        if (! $this->puedeAbrirse()) {
            throw new DomainException('El grupo no cumple con los requisitos para abrirse.');
        }

        $this->abierto = true;
        $this->save();
    }
}
