<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class ProductoController extends Controller
{

    /**
     * Esta funcion muestra la lista de productos
     */
    public function index()
    {
        $productos = Producto::all();
        $productos->load('categoria');
        $pdfRoute = route('producto.pdf');
        $csvRoute = route('producto.csv');
        return view('producto.index', compact('productos', 'pdfRoute','csvRoute'));
    }

    /**
     * Esta funcion muestra el formulario para crear un producto
     */
    public function create()
    {
        $categorias = Categoria::all();
        if ($categorias->isEmpty()) {
            // Si no hay categorías, muestra una alerta SweetAlert
            return redirect()->route('categorias.create')
                ->with('warning', 'No tienes categorías creadas. Debes crear al menos una.');
        }
        return view('producto.create', compact('categorias'));
    }

    /**
     * Esta funcion guarda un producto en la base de datos
     */
    public function store(Request $request)
    {
        $this->validarDatos($request);

        $producto = $this->crearProducto($request);

        return redirect('productos')->with('success', 'El producto se ha guardado.');
    }

    /**
     * Esta funcion valida los datos del formulario
     */
    public function validarDatos(Request $request, $update = false)
    {
        $reglas = [
            'nombre' => 'required',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categoria_id' => 'required',
        ];

        $mensajes = [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'precio.required' => 'El campo precio es obligatorio.',
            'precio.numeric' => 'El campo precio debe ser numérico.',
            'precio.min' => 'El campo precio debe ser mayor o igual a 0.',
            'categoria_id.required' => 'El campo categoría es obligatorio.',
            'stock.required' => 'El campo stock es obligatorio.',
            'stock.integer' => 'El campo stock debe ser un número entero.',
            'stock.min' => 'El campo stock debe ser mayor o igual a 0.',
        ];

        $request->validate($reglas, $mensajes);
    }

    /**
     * Esta función crea un producto a partir de los datos de la solicitud
     */
    public function crearProducto(Request $request)
    {
        $producto = new Producto();
        $producto->nombre = $request->input('nombre');
        $producto->precio = $request->input('precio');
        $producto->stock = $request->input('stock');
        $producto->categoria_id = $request->input('categoria_id');
        $producto->save();

        return $producto;
    }


    /**
     * Esta funcion muestra un producto en particular
     */
    public function show(Producto $producto)
    {
    }

    /**
     * Esta funcion muestra el formulario para editar un producto
     */
    public function edit($id)
    {
        $producto = Producto::find($id);
        $categorias = Categoria::all();
        return view('producto.edit', compact('producto', 'categorias'));
    }

    /**
     * Esta funcion actualiza un producto en la base de datos
     */
    public function update(Request $request, string $id)
    {
        $this->validarDatos($request, true);

        $producto = Producto::find($id);
        $producto->nombre = $request->get('nombre');
        $producto->precio = $request->get('precio');
        $producto->stock = $request->input('stock');
        $producto->categoria_id = $request->input('categoria_id');

        if ($request->hasFile('foto')) {
            if ($producto->foto) {
                Storage::delete($producto->foto);
            }
            $foto = $request->file('foto')->store('public/productos_imagenes');
            $url = Storage::url($foto);
            $producto->foto = $url;
        }
        $producto->save();

        return redirect('productos')->with('edit-success', 'El producto se ha actualizado exitosamente.');
    }

    /**
     * Esta funcion elimina un producto de la base de datos
     */
    public function destroy(string $id)
    {
        $producto = Producto::find($id);
        $producto->delete();
        return redirect('productos')->with('eliminar', 'ok');
    }

    /**
     * Esta funcion genera un PDF con la lista de productos REPORTE
     */
    public function generarPdf()
    {
        $productos = Producto::all();
        $dompdf = new Dompdf();

        $html = View::make('producto.pdf', compact('productos'))->render();
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->stream("Catalogo_Productos.pdf");
    }

    /**
     * Esta funcion genera un CSV con la lista de productos REPORTE
     */
    public function generarCsv()
    {
        $productos = Producto::all();
        $csvData = '';
        $csvHeader = ['ID', 'Nombre', 'Precio', 'Stock'];
        $csvData .= implode(',', $csvHeader) . "\n";
        foreach ($productos as $producto) {
            $csvRow = [
                $producto->id,
                $producto->nombre,
                $producto->precio,
                $producto->stock
            ];
            $csvData .= implode(',', $csvRow) . "\n";
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=Catalogo_Productos.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        $response = new Response($csvData, 200, $headers);
        return $response;
    }
}
