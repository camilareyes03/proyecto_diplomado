<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class PedidoController extends Controller
{

    /**
     * Muestra la página de índice de pedidos.
     */
    public function index()
    {
        $pedidos = Pedido::all();
        return $this->renderPedidoIndex($pedidos);
    }

    public function proforma()
    {
        $pedidos = Pedido::where('tipo_pedido', 'proforma')->get();
        return $this->renderPedidoIndex($pedidos);
    }

     /**
     * Muestra la página de índice de pedidos de tipo "Oficial".
     */
    public function oficial()
    {
        $pedidos = Pedido::where('tipo_pedido', 'oficial')->get();
        return $this->renderPedidoIndex($pedidos);
    }


    /**
     * Muestra la página de creación de pedidos.
     */
    public function create()
    {
        $clientes = $this->obtenerClientes();
        if ($clientes->isEmpty()) {
            return $this->redirigirACrearPersonas();
        }
        return view('pedido.create', compact('clientes'));
    }

    /**
     * Almacena un nuevo pedido en la base de datos.
     */
    public function store(Request $request)
    {
        $this->validarDatos($request);
        $pedido = $this->crearPedido($request);
        return $this->redirigirConMensaje('El pedido se ha guardado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pedido $pedido)
    {
        //
    }

    /**
     * Muestra la vista de edición de un pedido.
     *
     * @param int $id Identificador del pedido a editar.
     * @return \Illuminate\View\View Vista de edición de pedido.
     */
    public function edit($id)
    {
        $pedido = $this->obtenerPedido($id);
        $clientes = $this->obtenerClientes();

        return $this->mostrarVistaEdicion($pedido, $clientes);
    }

    /**
     * Actualiza un pedido en la base de datos.
     *
     * @param \Illuminate\Http\Request $request Solicitud HTTP con los datos de actualización.
     * @param \App\Models\Pedido $pedido Pedido a actualizar.
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito.
     */
    public function update(Request $request, Pedido $pedido)
    {
        $this->validarDatos($request);
        $tipoPedidoAnterior = $pedido->tipo_pedido;
        $tipoPedidoNuevo = $request->input('tipo_pedido');

        $this->actualizarStockProductos($pedido, $tipoPedidoAnterior, $tipoPedidoNuevo);
        $this->actualizarPedido($pedido, $request, $tipoPedidoNuevo);
        return $this->redirigirConMensaje('El pedido se ha actualizado exitosamente.');
    }

    /**
     * Elimina un pedido específico del almacenamiento.
     *
     * @param string $id Identificador del pedido a eliminar.
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito.
     */
    public function destroy(string $id)
    {
        $pedido = $this->obtenerPedido($id);
        $this->eliminarPedido($pedido);

        return $this->redirigirConMensaje('Pedido eliminado exitosamente.');
    }

        /**
     * Renderiza la vista de la página de índice de pedidos.
     *
     * @param \Illuminate\Database\Eloquent\Collection $pedidos Colección de pedidos
     * @return \Illuminate\View\View Vista de la página de índice de pedidos
     */
    private function renderPedidoIndex($pedidos)
    {
        $productos = Producto::all();
        $categorias = Categoria::all();
        $reportes = $this->generarEnlacesReportes($pedidos);

        return view('pedido.index', compact( 'reportes','productos', 'pedidos', 'categorias'));
    }


    /**
    * Carga y devuelve los productos asociados a una categoría específica.
    *
    * @param int $categoriaId Identificador de la categoría de la cual cargar productos.
    * @return \Illuminate\Http\JsonResponse Respuesta JSON con los productos.
    */
    public function cargarProductosPorCategoria($categoriaId)
    {
        $productos = Producto::where('categoria_id', $categoriaId)->get();
        return response()->json($productos);
    }

    /**
    * Obtiene una colección de clientes.
    *
    * @return \Illuminate\Database\Eloquent\Collection Colección de clientes.
    */
    private function obtenerClientes()
    {
        return User::where('tipo_usuario', 'cliente')->get();
    }

    /**
    * Redirige al formulario de creación de personas si no hay clientes registrados.
    *
    * @return \Illuminate\Http\RedirectResponse
    */
    private function redirigirACrearPersonas()
    {
    return redirect()->route('personas.crear')
        ->with('warning', 'No tienes clientes registrados. Debes registrar al menos uno.');
    }

    /**
     * Valida los datos de la solicitud.
     *
     * @param \Illuminate\Http\Request $request Solicitud HTTP con los datos del pedido.
     */
    public function validarDatos(Request $request)
    {
        $rules = [
            'fecha' => 'required|date',
            'cliente_id' => 'required|exists:users,id',
            'tipo_pedido' => 'required|in:proforma,oficial',
        ];

        $messages = [
            'fecha.required' => 'El campo Fecha es obligatorio.',
            'fecha.date' => 'El campo Fecha debe ser una fecha válida.',
            'cliente_id.required' => 'El campo Cliente es obligatorio.',
            'cliente_id.exists' => 'El Cliente seleccionado no es válido.',
            'tipo_pedido.required' => 'El campo Tipo de Pedido es obligatorio.',
            'tipo_pedido.in' => 'El Tipo de Pedido debe ser "proforma" o "oficial.', // Nuevo mensaje para tipo_pedido
        ];

        $request->validate($rules, $messages);
    }

    /**
    * Crea un nuevo objeto Pedido y lo almacena en la base de datos.
    *
    * @param \Illuminate\Http\Request $request Solicitud HTTP con los datos del pedido.
    * @return \App\Models\Pedido El pedido recién creado.
    */
    private function crearPedido(Request $request)
    {
        $pedido = new Pedido();
        $pedido->fecha = $request->input('fecha');
        $pedido->cliente_id = $request->input('cliente_id');

        if ($request->input('tipo_pedido') === 'oficial') {
            $pedido->tipo_pago = $request->input('tipo_pago');
        } else {
            $pedido->tipo_pago = null;
        }
        $pedido->tipo_pedido = $request->input('tipo_pedido');
        $pedido->total = 0.0;
        $pedido->save();
        return $pedido;
    }

    /**
     * Redirige a la página de pedidos con un mensaje de éxito.
     *
     * @param string $mensaje Mensaje de éxito.
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito.
     */
    private function redirigirConMensaje($mensaje)
    {
        return redirect('pedidos')->with('success', $mensaje);
    }

    /**
    * Obtiene un pedido por su ID.
    *
    * @param int $id Identificador del pedido a obtener.
    * @return \App\Models\Pedido|mixed El pedido recuperado.
    */
    private function obtenerPedido($id)
    {
       // Recupera y devuelve un pedido por su ID.
       return Pedido::find($id);
    }

    /**
     * Muestra la vista de edición de pedido con los datos del pedido y los clientes.
     *
     * @param \App\Models\Pedido $pedido Pedido a editar.
     * @param \Illuminate\Database\Eloquent\Collection $clientes Colección de clientes.
     * @return \Illuminate\View\View Vista de edición de pedido.
     */
    private function mostrarVistaEdicion($pedido, $clientes)
    {
        return view('pedido.edit', compact('pedido', 'clientes'));
    }

    /**
     * Actualiza el stock de productos en función del tipo de pedido anterior y nuevo.
     *
     * @param \App\Models\Pedido $pedido Pedido a actualizar.
     * @param string $tipoPedidoAnterior Tipo de pedido anterior.
     * @param string $tipoPedidoNuevo Tipo de pedido nuevo.
     */
    private function actualizarStockProductos(Pedido $pedido, $tipoPedidoAnterior, $tipoPedidoNuevo)
    {
        if ($tipoPedidoAnterior !== $tipoPedidoNuevo) {
            $detallesPedido = DetallePedido::where('pedido_id', $pedido->id)->get();

            foreach ($detallesPedido as $detalle) {
                $producto = Producto::find($detalle->producto_id);
                if ($tipoPedidoNuevo === 'oficial') {
                    $producto->stock -= $detalle->cantidad;
                } elseif ($tipoPedidoAnterior === 'oficial') {
                    $producto->stock += $detalle->cantidad;
                }
                $producto->save();
            }
        }
    }

    /**
     * Actualiza la información del pedido.
     *
     * @param \App\Models\Pedido $pedido Pedido a actualizar.
     * @param \Illuminate\Http\Request $request Solicitud HTTP con los datos de actualización.
     * @param string $tipoPedidoNuevo Tipo de pedido nuevo.
     */
    private function actualizarPedido(Pedido $pedido, Request $request, $tipoPedidoNuevo)
    {
        $pedido->fecha = $request->input('fecha');
        $pedido->cliente_id = $request->input('cliente_id');

        if ($tipoPedidoNuevo === 'oficial') {
            $pedido->tipo_pago = $request->input('tipo_pago');
        } else {
            $pedido->tipo_pago = null;
        }

        $pedido->tipo_pedido = $tipoPedidoNuevo;
        $pedido->save();
    }

    /**
     * Elimina un pedido de la base de datos.
     *
     * @param \App\Models\Pedido $pedido Pedido a eliminar.
     */
    private function eliminarPedido(Pedido $pedido)
    {
        $pedido->delete();
    }

    public function descargarPdf($id)
    {
        $pedido = Pedido::find($id);

        $pdf = new Dompdf();
        $html = view('pedido.pdf', compact('pedido'))->render();
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->stream("pedido_{$id}.pdf");
    }

    public function descargarCsv($id)
    {
        $pedido = Pedido::find($id);
        $detalles = $pedido->detallePedido;

        $csvData = '';
        $csvHeader = ['Producto', 'Cantidad', 'Monto'];
        $csvData .= implode(',', $csvHeader) . "\n";
        foreach ($detalles as $detalle) {
            $csvRow = [
                $detalle->producto->nombre,
                $detalle->cantidad,
                $detalle->monto,
            ];
            $csvData .= implode(',', $csvRow) . "\n";
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=pedido_{$id}_detalles.csv",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
        $response = new Response($csvData, 200, $headers);

        return $response;
    }

        /**
    * Genera enlaces de descarga de PDF y CSV
    *
    */
    private function generarEnlacesReportes($pedidos)
    {
        return $pedidos->map(function ($pedido) {
            return [
                'pedido' => $pedido,
                'pdfRoute' => route('pedido.pdf', ['id' => $pedido->id]),
                'csvRoute' => route('pedido.csv', ['id' => $pedido->id]),
            ];
        });
    }

}
