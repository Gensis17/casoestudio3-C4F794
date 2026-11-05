<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CursoController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'sigla' => ['required', 'string', 'max:255'],
            'nombre_curso' => ['required_without:nombreCurso', 'string', 'max:255'],
            'nombreCurso' => ['required_without:nombre_curso', 'string', 'max:255'],
            'creditos' => ['required', 'integer'],
        ]);

        $nombre = $request->input('nombre_curso', $request->input('nombreCurso'));

        $curso = Curso::create([
            'sigla' => $validatedData['sigla'],
            'nombre_curso' => $nombre,
            'creditos' => $validatedData['creditos'],
        ]);

        return response()->json([
            'message' => 'Curso creado exitosamente',
            'data' => $curso,
        ], 201);
    }
}
