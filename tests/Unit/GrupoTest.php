<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\Grupo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->curso = Curso::factory()->create([
        'sigla' => 'IF6100',
        'nombre_curso' => 'Análisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);

    $this->grupo = Grupo::factory()->for($this->curso)->create([
        'numero_ciclo' => 2,
        'anio' => 2026,
        'numero_grupo' => 1,
        'abierto' => false,
    ]);

    $this->docente1001 = Docente::factory()->create([
        'cod_empleado' => 1001,
        'nombre_empleado' => 'Ana',
        'apellidos_empleado' => 'Mora Vargas',
    ]);

    $this->docente1002 = Docente::factory()->create([
        'cod_empleado' => 1002,
        'nombre_empleado' => 'Luis',
        'apellidos_empleado' => 'Rojas Solano',
    ]);

    $this->docente1003 = Docente::factory()->create([
        'cod_empleado' => 1003,
        'nombre_empleado' => 'Carla',
        'apellidos_empleado' => 'Jiménez Araya',
    ]);

    $this->estudianteC12345 = Estudiante::factory()->create([
        'carne' => 'C12345',
        'nombre' => 'María',
        'apellidos' => 'Solís Quesada',
        'consejero_id' => $this->docente1001->id,
    ]);
});

/**
 * Matricula la cantidad indicada de estudiantes de factory en el grupo.
 * Se fija el consejero para no generar docentes aleatorios cuyo código choque con 1001-1003.
 */
function matricularEstudiantes(Grupo $grupo, Docente $consejero, int $cantidad): void
{
    $grupo->estudiantes()->attach(
        Estudiante::factory()->count($cantidad)->create(['consejero_id' => $consejero->id])
    );
}

it('CP-01 guarda el grupo 01 de IF6100 abierto y lo recupera con su curso', function () {
    $this->grupo->update(['abierto' => true]);

    $grupo = Grupo::find($this->grupo->id);
    $curso = $grupo->curso;

    expect($grupo->numero_ciclo)->toBe(2)
        ->and($grupo->anio)->toBe(2026)
        ->and($grupo->numero_grupo)->toBe(1)
        ->and($grupo->abierto)->toBeTrue()
        ->and($curso->sigla)->toBe('IF6100')
        ->and($curso->creditos)->toBe(4);

    $this->assertDatabaseCount('grupos', 1);
});

it('CP-02 un curso tiene varios grupos y se filtran los abiertos', function () {
    $this->grupo->update(['abierto' => true]);

    $grupo2 = Grupo::factory()->for($this->curso)->create([
        'numero_ciclo' => 2, 'anio' => 2026, 'numero_grupo' => 2, 'abierto' => true,
    ]);
    Grupo::factory()->for($this->curso)->create([
        'numero_ciclo' => 2, 'anio' => 2026, 'numero_grupo' => 3, 'abierto' => false,
    ]);

    $grupos = $this->curso->fresh()->grupos;
    $abiertos = $grupos->where('abierto', true);

    expect($grupos)->toHaveCount(3)
        ->and($abiertos)->toHaveCount(2)
        ->and($abiertos->pluck('id')->all())->toEqualCanonicalizing([$this->grupo->id, $grupo2->id])
        ->and($grupos->pluck('curso_id')->unique()->all())->toBe([$this->curso->id]);
});

it('CP-03 asigna dos docentes al grupo 01', function () {
    $this->grupo->asignarDocente($this->docente1001);
    $this->grupo->asignarDocente($this->docente1002);

    expect($this->grupo->docentes->pluck('cod_empleado')->all())->toEqualCanonicalizing([1001, 1002])
        ->and($this->docente1001->grupos->pluck('id')->all())->toContain($this->grupo->id);

    $this->assertDatabaseCount('docente_grupo', 2);
});

it('CP-04 rechaza un tercer docente en el grupo 01', function () {
    $this->grupo->asignarDocente($this->docente1001);
    $this->grupo->asignarDocente($this->docente1002);

    expect(fn () => $this->grupo->asignarDocente($this->docente1003))->toThrow(DomainException::class);

    expect($this->grupo->docentes()->count())->toBe(2);

    $this->assertDatabaseMissing('docente_grupo', [
        'grupo_id' => $this->grupo->id,
        'docente_id' => $this->docente1003->id,
    ]);
});

it('CP-05 no abre el grupo con 14 estudiantes matriculados', function () {
    $this->grupo->asignarDocente($this->docente1001);
    matricularEstudiantes($this->grupo, $this->docente1001, 14);

    expect($this->grupo->puedeAbrirse())->toBeFalse()
        ->and(fn () => $this->grupo->abrir())->toThrow(DomainException::class)
        ->and($this->grupo->fresh()->abierto)->toBeFalse();

    $this->assertDatabaseHas('grupos', ['id' => $this->grupo->id, 'abierto' => false]);
});

it('CP-06 abre el grupo con 15 estudiantes matriculados', function () {
    $this->grupo->asignarDocente($this->docente1001);
    matricularEstudiantes($this->grupo, $this->docente1001, 15);

    expect($this->grupo->puedeAbrirse())->toBeTrue();

    $this->grupo->abrir();

    expect($this->grupo->fresh()->abierto)->toBeTrue();
});
