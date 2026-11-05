<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('se guarda el curso en la base de datos via http', function () {
    $datosCurso = [
        'sigla' => 'IF6100',
        'nombre_curso' => 'Analisis y Diseño de Sistemas',
        'creditos' => 4,
    ];

    $response = $this->postJson('/cursos', $datosCurso);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'sigla' => 'IF6100',
            'nombre_curso' => 'Analisis y Diseño de Sistemas',
            'creditos' => 4,
        ]);

    $this->assertDatabaseHas('cursos', [
        'sigla' => 'IF6100',
        'nombre_curso' => 'Analisis y Diseño de Sistemas',
        'creditos' => 4,
    ]);
});

test('se guarda el curso en la base de datos via api y acepta formato camelCase', function () {
    $datosCurso = [
        'sigla' => 'IF6200',
        'nombreCurso' => 'Bases de Datos',
        'creditos' => 4,
    ];

    $response = $this->postJson('/api/cursos', $datosCurso);

    $response->assertStatus(201);

    $this->assertDatabaseHas('cursos', [
        'sigla' => 'IF6200',
        'nombre_curso' => 'Bases de Datos',
        'creditos' => 4,
    ]);
});
