<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class UserController extends Controller
{
    public function index()
    {
        $personas = User::all();
        $pdfRoute = route('user.pdf', ['tipo' => 'todos']);
        $csvRoute = route('user.csv', ['tipo' => 'todos']);
        return view('persona.index', compact('personas', 'pdfRoute', 'csvRoute'));
    }

    public function clientes()
    {
        $personas = User::where('tipo_usuario', 'cliente')->get();
        $pdfRoute = route('user.pdf', ['tipo' => 'cliente']);
        $csvRoute = route('user.csv', ['tipo' => 'cliente']);
        return view('persona.index', compact('personas', 'pdfRoute', 'csvRoute'));
    }

    public function administradores()
    {
        $personas = User::where('tipo_usuario', 'administrador')->get();
        $pdfRoute = route('user.pdf', ['tipo' => 'administrador']);
        $csvRoute = route('user.csv', ['tipo' => 'administrador']);
        return view('persona.index', compact('personas', 'pdfRoute', 'csvRoute'));
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create()
    {
        return view('persona.create');
    }

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     */
    public function store(Request $request)
    {
        $this->validarDatos($request);

        $persona = new User();
        $persona->name = $request->input('name');
        $persona->ci = $request->input('ci');
        $persona->telefono = $request->input('telefono');
        $persona->tipo_usuario = $request->input('tipo_usuario');

        if ($request->input('tipo_usuario') === 'cliente') {
            $this->validarFoto($request, $persona);
        } else {
            $persona->email = $request->input('email');
            $persona->password = bcrypt($request->input('password'));
        }

        $persona->save();

        return redirect('personas')->with('success', 'La persona se ha guardado exitosamente.');
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(string $id)
    {
        $persona = User::findOrFail($id);
        return view('persona.show', compact('persona'));
    }

    /**
     * Muestra el formulario para editar el recurso especificado.
     */
    public function edit(string $id)
    {
        $persona = User::findOrFail($id);
        return view('persona.edit', compact('persona'));
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     */
    public function update(Request $request, $id)
    {
        $this->validarDatos($request);

        $persona = User::find($id);

        if (!$persona) {
            return redirect('personas')->with('error', 'Persona no encontrada.');
        }

        $persona->name = $request->input('name');
        $persona->ci = $request->input('ci');
        $persona->telefono = $request->input('telefono');
        $persona->tipo_usuario = $request->input('tipo_usuario');

        if ($request->input('tipo_usuario') === 'cliente') {
            $this->validarFoto($request, $persona);
        } else {
            $persona->email = $request->input('email');
            if ($request->input('password')) {
                $persona->password = bcrypt($request->input('password'));
            }
        }

        $persona->save();
        return redirect('personas')->with('success', 'La persona se ha actualizado exitosamente.');
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     */
    public function destroy(string $id)
    {
        $persona = User::find($id);
        $persona->delete();
        return redirect('personas')->with('eliminar', 'ok');
    }

    /**
     * Este metodo valida los datos para registrar y editar usuario.
     */
    public function validarDatos(Request $request)
    {
        $reglas = [
            'name' => 'required',
            'ci' => 'min:7',
            'telefono' => 'min:8',
            'tipo_usuario' => ['required', 'not_in:nulo'],
        ];

        $mensajes = [
            'name.required' => 'Este campo es obligatorio.',
            'ci.min' => 'Este campo debe tener mínimo 7 valores.',
            'telefono.min' => 'Este campo debe tener un mínimo de 8 dígitos.',
            'tipo_usuario.not_in' => 'Por favor, selecciona una opción válida.',
        ];

        $request->validate($reglas, $mensajes);
    }

    /**
     * Este metodo valida los si existe la foto para registrar y editar usuario.
     */
    public function validarFoto(Request $request, $persona)
    {
        if ($request->hasFile('foto')) {
            $request->validate([
                'foto' => ['image', 'nullable', 'max:2048', 'mimes:png,jpg,jpeg,gif'],
            ]);

            $foto = $request->file('foto')->store('public/imagenes/clientes');
            $url = Storage::url($foto);
            $persona->foto = $url;
        }
        $persona->email = null;
        $persona->password = null;
    }

    /**
     * Generar PDF de los usuarios
     */

    public function generarPdf($tipo)
    {
        if ($tipo === 'todos') {
            $users = User::all();
        } else {
            $users = User::where('tipo_usuario', $tipo)->get();
        }
        $dompdf = new Dompdf();
        $html = View::make('persona.pdf', compact('users'))->render();

        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->stream("listado_{$tipo}_usuarios.pdf");
    }

    /**
     * Generar Csv de los usuarios
     */
    public function generarCsv($tipo)
    {
        if ($tipo === 'todos') {
            $users = User::all();
        } else {
            $users = User::where('tipo_usuario', $tipo)->get();
        }
        $csvData = '';
        $csvHeader = ['ID', 'Nombre Completo', 'Telefono', 'CI', 'Tipo de Usuario'];
        $csvData .= implode(',', $csvHeader) . "\n";
        foreach ($users as $user) {
            $csvRow = [
                $user->id,
                $user->name,
                $user->telefono,
                $user->ci,
                $user->tipo_usuario,
            ];
            $csvData .= implode(',', $csvRow) . "\n";
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=listado_' . $tipo . '_usuarios.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $response = new Response($csvData, 200, $headers);
        return $response;
    }

}
