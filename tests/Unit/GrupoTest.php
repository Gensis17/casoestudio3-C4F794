<?php

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Estudiante;
use App\Models\EstudianteMatriculado;
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

    $this->docentes = Docente::factory()->count(2)->create();

    $this->grupo = Grupo::factory()->for($this->curso)->create([
        'numero_ciclo' => 2,
        'anio' => 2026,
        'numero_grupo' => 1,
        'abierto' => true,
    ]);

    $this->grupo->docentes()->attach($this->docentes);

    $this->grupo->estudiantes()->attach(
        Estudiante::factory()->count(15)->create(),
        ['nota' => 85.5]
    );
});

test('el grupo contiene sus datos propios', function () {
    $grupo = Grupo::find($this->grupo->id);

    expect($grupo)->toBeInstanceOf(Grupo::class);
    expect($grupo->only(['numero_ciclo', 'anio', 'numero_grupo', 'abierto']))
        ->toBe(['numero_ciclo' => 2, 'anio' => 2026, 'numero_grupo' => 1, 'abierto' => true]);
});

test('el grupo pertenece a un curso', function () {
    $curso = Grupo::find($this->grupo->id)->curso;

    expect($curso)->toBeInstanceOf(Curso::class);
    expect($curso->sigla)->toBe('IF6100');
});

test('el grupo es impartido por 1 o 2 docentes', function () {
    $docentes = Grupo::find($this->grupo->id)->docentes;

    expect($docentes->count())->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(2);
    expect($docentes->pluck('id')->all())->toEqualCanonicalizing($this->docentes->pluck('id')->all());
});

test('el grupo tiene al menos 15 estudiantes matriculados', function () {
    $estudiantes = Grupo::find($this->grupo->id)->estudiantes;

    expect($estudiantes->count())->toBeGreaterThanOrEqual(15);
});

test('cada estudiante matriculado tiene su nota registrada', function () {
    $estudiante = Grupo::find($this->grupo->id)->estudiantes->first();

    expect($estudiante->pivot)->toBeInstanceOf(EstudianteMatriculado::class);
    expect($estudiante->pivot->nota)->toBe(85.5);
});

test('cada estudiante del grupo tiene un profesor consejero', function () {
    $estudiante = Grupo::find($this->grupo->id)->estudiantes->first();

    expect($estudiante->consejero)->toBeInstanceOf(Docente::class);
});