<?php

namespace App\Http\Controllers;

use App\Models\Ubicacion;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    /**
    * Muestra una lista de ubicaciones asociadas a un cliente específico.
    *
    * @param int $cliente_id El identificador del cliente para el cual se listarán las ubicaciones.
    * @return View vista que muestra la lista de ubicaciones.
    */
    public function index($cliente_id)
    {
        $ubicaciones = Ubicacion::all()->where('cliente_id', $cliente_id);
        return view('ubicacion.index', compact('ubicaciones', 'cliente_id'));
    }

    /**
    * Muestra el formulario para crear una nueva ubicación asociada a un cliente específico.
    *
    * @param int $cliente_id El identificador del cliente al que se asociará la nueva ubicación.
    * @return View La vista que muestra el formulario de creación de ubicación.
    */
    public function create($cliente_id)
    {
        return view('ubicacion.create', compact('cliente_id'));
    }

    /**
    * Almacena una nueva ubicación en la base de datos.
    *
    * @param Request $request La solicitud que contiene los datos de la ubicación a crear.
    * @param int $cliente_id El identificador del cliente al que se asocia la ubicación.
    * @return RedirectResponse Una redirección a la vista de lista de ubicaciones con un mensaje de éxito.
    */
    public function store(Request $request, $cliente_id)
    {
        $this->validarDatos($request);

        $ubicacion = new Ubicacion();
        $ubicacion->nombre = $request->input('nombre');
        $ubicacion->referencia = $request->input('referencia');
        $ubicacion->link = $request->input('link');
        $ubicacion->latitud = $request->input('latitud');
        $ubicacion->longitud = $request->input('longitud');
        $ubicacion->cliente_id = $cliente_id;

        $ubicacion->save();

        return redirect()->route('ubicaciones.index', $cliente_id)->with('success', 'La ubicación se ha guardado exitosamente.');
    }

    /**
    * Muestra el formulario para editar una ubicación específica.
    *
    * @param int $ubicacion_id El identificador de la ubicación que se desea editar.
    * @return View La vista que muestra el formulario de edición de ubicación.
    */
    public function edit($ubicacion_id)
    {
        $ubicacion = Ubicacion::findOrFail($ubicacion_id);
        return view('ubicacion.edit', compact('ubicacion'));
    }

    /**
    * Actualiza una ubicación específica en la base de datos.
    *
    * @param Request $request La solicitud que contiene los datos actualizados de la ubicación.
    * @param int $ubicacion_id El identificador de la ubicación que se desea actualizar.
    * @return RedirectResponse Una redirección a la vista de lista de ubicaciones con un mensaje de éxito.
    */
    public function update(Request $request, $ubicacion_id)
    {
        $this->validarDatos($request);

        $ubicacion = Ubicacion::findOrFail($ubicacion_id);
        $ubicacion->nombre = $request->input('nombre');
        $ubicacion->referencia = $request->input('referencia');
        $ubicacion->link = $request->input('link');
        $ubicacion->latitud = $request->input('latitud');
        $ubicacion->longitud = $request->input('longitud');

        $ubicacion->save();

        return redirect()->route('ubicaciones.index', $ubicacion->cliente_id)->with('edit-success', 'La ubicación se ha actualizado exitosamente.');
    }

    /**
    * Elimina una ubicación específica del almacenamiento y la base de datos.
    *
    * @param int $ubicacion_id El identificador de la ubicación que se desea eliminar.
    * @return RedirectResponse Una redirección a la vista de lista de ubicaciones con un mensaje de eliminación exitosa.
    */
    public function destroy($ubicacion_id)
    {
        $ubicacion = Ubicacion::findOrFail($ubicacion_id);
        $cliente_id = $ubicacion->cliente_id;
        $ubicacion->delete();
        return redirect()->route('ubicaciones.index', $cliente_id)->with('eliminar', 'ok');
    }

    /**
    * Valida los datos necesarios para registrar y editar una ubicación.
    *
    * @param Request $request La solicitud que contiene los datos a validar.
    * @return void
    */
    public function validarDatos(Request $request)
    {
        $reglas = [
            'nombre' => 'required',
            'latitud' => 'required',
            'longitud' => 'required',
        ];

        $mensajes = [
            'nombre.required' => 'Coloque un nombre a la ubicacion.',
            'latitud.required' => 'Este campo es obligatorio.',
            'longitud.required' => 'Este campo es obligatorio.',
        ];

        $request->validate($reglas, $mensajes);
    }
}
