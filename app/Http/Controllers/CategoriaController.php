<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class CategoriaController extends Controller
{
    /**
     * Esta funcion muestra todas las categorias
     */
    public function index()
    {
        $categorias = Categoria::all();
        return view('categoria.index', compact('categorias',));
    }

/**
 * Esta funcion muestra el formulario para crear una categoria
 */
    public function create()
    {
        return view('categoria.create');
    }

/**
 *  Esta funcion guarda una categoria
 */
    public function store(Request $request)
    {
        $this->validarDatos($request);

        $categoria = new Categoria();
        $categoria->nombre = $request->input('nombre');
        $categoria->descripcion = $request->input('descripcion');

        $categoria->save();

        return redirect('categorias')->with('success', 'La categoría se ha guardado exitosamente.');
    }

    /**
     * Esta funcion muestra una categoria

     */
    public function edit($id)
    {
        $categoria = Categoria::find($id);
        return view('categoria.edit', compact('categoria'));
    }

    /**
     * Esta funcion actualiza una categoria
     */
    public function update(Request $request, string $id)
    {
        $categoria = Categoria::find($id);
        $this->validarDatos($request);
        $categoria->nombre = $request->input('nombre');
        $categoria->descripcion = $request->input('descripcion');
        $categoria->save();
        return redirect('categorias')->with('edit-success', 'La categoría se ha actualizado exitosamente.');
    }
    /**
     * Esta funcion elimina una categoria
     */
    public function destroy(string $id)
    {
        $categoria = Categoria::find($id);
        $categoria->delete();
        return redirect('categorias')->with('eliminar', 'ok');
    }


    /**
     * Esta funcion valida los datos de una categoria
     */
    private function validarDatos(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'required',
        ],
        [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'descripcion.required' => 'El campo descripcion es obligatorio.',
        ]);
    }


}
