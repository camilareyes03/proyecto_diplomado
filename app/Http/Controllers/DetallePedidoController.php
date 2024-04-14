<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DetallePedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //vista de detalle pedido
    }


    /**
     * Muestra el formulario de creación de un detalle de pedido.
     *
     * @param \Illuminate\Http\Request $request Solicitud HTTP que contiene el ID del pedido.
     * @return \Illuminate\View\View Vista de creación de detalle de pedido.
     */
    public function create(Request $request)
    {
        $pedidoId = $request->input('pedido_id');
        $productos = $this->obtenerProductos();
        return $this->mostrarVistaCreacion($productos, $pedidoId);
    }

    /**
     * Almacena un detalle de pedido en la base de datos.
     *
     * @param \Illuminate\Http\Request $request Solicitud HTTP con los datos del detalle de pedido.
     * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito.
     */
    public function store(Request $request)
    {
        $this->validarDetallePedido($request);

        $productoId = $request->input('producto_id');
        $cantidad = $request->input('cantidad');
        $pedidoId = $request->input('pedido_id');

        $detallePedidoExistente = $this->obtenerDetallePedidoExistente($pedidoId, $productoId);

        if ($detallePedidoExistente) {
            $this->actualizarDetallePedidoExistente($detallePedidoExistente, $cantidad);
        } else {
            $this->crearNuevoDetallePedido($pedidoId, $productoId, $cantidad);
        }

        $this->actualizarStockProducto($pedidoId, $productoId, $cantidad);
        $this->actualizarMontoTotalPedido($pedidoId);

        return redirect()->route('pedidos.index')->with('success-detalle', 'El producto se ha guardado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pedido = Pedido::findOrFail($id);
        $detalles = $this->obtenerDetallesDelPedido($pedido);
        $productos = $this->obtenerProductos();
        $montoTotal = $this->calcularMontoTotal($detalles);

        return view('detalle_pedido.index', compact('detalles', 'productos', 'montoTotal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $detalle = DetallePedido::find($id);
        $productos =  $this->obtenerProductos();
        return view('detalle_pedido.edit', compact('detalle', 'productos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'producto_id' => 'required|exists:producto,id',
            'cantidad' => 'required|numeric|min:1',
        ]);

        $detalle = DetallePedido::findOrFail($id);

        $nuevaCantidad = $request->input('cantidad');
        $nuevoProductoId = $request->input('producto_id');
        $pedidoId = $detalle->pedido_id;

        $diferenciaCantidad = $nuevaCantidad - $detalle->cantidad;

        $detalle->producto_id = $nuevoProductoId;
        $detalle->cantidad = $nuevaCantidad;

        $producto = Producto::find($nuevoProductoId);
        $precioProducto = $producto->precio;

        $detalle->monto = $precioProducto * $nuevaCantidad;

        $detalle->save();

        $producto->stock -= $diferenciaCantidad;
        $producto->save();

        $pedido = Pedido::find($pedidoId);
        $pedido->actualizarMontoTotal();

        return redirect('/pedidos')->with('success', 'El detalle del pedido se ha actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $detalle = DetallePedido::find($id);
        $pedidoId = $detalle->pedido_id;
        $cantidadEliminada = $detalle->cantidad;
        $producto = $detalle->producto;
        $producto->stock += $cantidadEliminada;
        $producto->save();
        $detalle->delete();
        $pedido = Pedido::find($pedidoId);
        $pedido->actualizarMontoTotal();

        return redirect('pedidos')->with('eliminar-detalle', 'ok');
    }


    /**
     * Muestra la vista de creación de detalle de pedido.
     *
     * @param \Illuminate\Database\Eloquent\Collection $productos Colección de productos.
     * @param int $pedidoId ID del pedido relacionado.
     * @return \Illuminate\View\View Vista de creación de detalle de pedido.
     */
    private function mostrarVistaCreacion($productos, $pedidoId)
    {
        return view('detalle_pedido.create', compact('productos', 'pedidoId'));
    }

    /**
     * Valida los datos del detalle de pedido proporcionados en la solicitud.
     *
     * @param \Illuminate\Http\Request $request Solicitud HTTP con los datos del detalle de pedido.
     */
    protected function validarDetallePedido(Request $request)
    {
        return $request->validate([
            'producto_id' => 'required',
            'cantidad' => [
                'required',
                'numeric',
                'min:1',
                Rule::unique('detalle_pedido')->where(function ($query) use ($request) {
                    return $query->where('producto_id', $request->input('producto_id'))
                        ->where('pedido_id', $request->input('pedido_id'));
                }),
            ],
            'pedido_id' => 'required',
        ], [
            'producto_id.required' => 'El campo producto es obligatorio.',
            'cantidad.required' => 'El campo cantidad es obligatorio.',
            'cantidad.numeric' => 'El campo cantidad debe ser numérico.',
            'cantidad.min' => 'El campo cantidad debe ser mayor o igual a 1.',
            'cantidad.unique' => 'Este producto ya existe en el detalle del pedido. Actualiza la cantidad si es necesario.',
            'pedido_id.required' => 'El campo pedido es obligatorio.',
        ]);
    }


    /**
     * Obtiene un detalle de pedido existente por ID de pedido y ID de producto.
     *
     * @param int $pedidoId ID del pedido.
     * @param int $productoId ID del producto.
     * @return \App\Models\DetallePedido|null El detalle de pedido existente o null si no se encuentra.
     */
    private function obtenerDetallePedidoExistente($pedidoId, $productoId)
    {
        $detallePedidoExistente = DetallePedido::where('pedido_id', $pedidoId)
        ->where('producto_id', $productoId)
        ->first();
        return $detallePedidoExistente;
    }

    /**
     * Actualiza un detalle de pedido existente con una nueva cantidad y monto.
     *
     * @param \App\Models\DetallePedido $detallePedidoExistente Detalle de pedido existente.
     * @param int $cantidad Nueva cantidad.
     */
    private function actualizarDetallePedidoExistente($detallePedidoExistente, $nuevaCantidad)
    {
        $detallePedidoExistente->cantidad = $nuevaCantidad;
        $nuevoMonto = $nuevaCantidad * $detallePedidoExistente->producto->precio;
        $detallePedidoExistente->monto = $nuevoMonto;

        $detallePedidoExistente->save();
    }

    /**
     * Crea un nuevo detalle de pedido.
     *
     * @param int $pedidoId ID del pedido.
     * @param int $productoId ID del producto.
     * @param int $cantidad Cantidad del producto.
     */
    private function crearNuevoDetallePedido($pedidoId, $productoId, $cantidad)
    {
        $detallePedido = new DetallePedido();
        $detallePedido->pedido_id = $pedidoId;
        $detallePedido->producto_id = $productoId;
        $detallePedido->cantidad = $cantidad;

        $producto = $this->obtenerProductoPorId($productoId);
        $precioProducto = $producto->precio;
        $monto = $cantidad * $precioProducto;
        $detallePedido->monto = $monto;

        $detallePedido->save();
    }

    /**
     * Obtiene un producto por su ID.
     *
     * @param int $productoId ID del producto.
     * @return \App\Models\Producto|null El producto encontrado o null si no se encuentra.
     */
    private function obtenerProductoPorId($productoId)
    {
        $producto = Producto::find($productoId);
        return $producto;
    }

    /**
     * Actualiza el stock de un producto si el pedido es de tipo 'oficial'.
     *
     * @param int $pedidoId ID del pedido.
     * @param int $productoId ID del producto.
     * @param int $cantidad Cantidad del producto.
     */
    private function actualizarStockProducto($pedidoId, $productoId, $cantidad)
    {

        $tipoPedido = Pedido::find($pedidoId)->tipo_pedido;

        if ($tipoPedido === 'oficial') {
            $producto = $this->obtenerProductoPorId($productoId);
            $producto->stock -= $cantidad;
            $producto->save();
        }
    }

    /**
     * Actualiza el monto total de un pedido.
     *
     * @param int $pedidoId ID del pedido.
     */
    private function actualizarMontoTotalPedido($pedidoId)
    {
        $pedido = Pedido::find($pedidoId);
        $pedido->actualizarMontoTotal();
    }

    /**
     * Obtiene los detalles de un pedido.
     *
     * @param \App\Models\Pedido $pedido
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function obtenerDetallesDelPedido($pedido)
    {
        return $pedido->detallePedido;
    }

    /**
     * Obtiene todos los productos.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function obtenerProductos()
    {
        return Producto::all();
    }

    /**
     * Calcula el monto total de los detalles de pedido.
     *
     * @param \Illuminate\Database\Eloquent\Collection $detalles
     * @return float
     */
    private function calcularMontoTotal($detalles)
    {
        return $detalles->sum('monto');
    }

    /**
     * Actualiza un detalle de pedido con los nuevos valores.
     *
     * @param \App\Models\DetallePedido $detalle
     * @param int $nuevaCantidad
     * @param int $nuevoProductoId
     */
    private function actualizarDetalle($detalle, $nuevaCantidad, $nuevoProductoId)
    {
        $producto = $this->obtenerProductoPorId($nuevoProductoId);
        $precioProducto = $producto->precio;

        $detalle->producto_id = $nuevoProductoId;
        $detalle->cantidad = $nuevaCantidad;
        $detalle->monto = $precioProducto * $nuevaCantidad;

        $detalle->save();
    }

    /**
     * Actualiza el stock de un producto en función de la diferencia de cantidad.
     *
     * @param \App\Models\DetallePedido $detalle
     * @param int $nuevaCantidad
     */
    private function actualizarStockProductoDetalle($detalle, $nuevaCantidad)
    {
        $diferenciaCantidad = $nuevaCantidad - $detalle->cantidad;
        $producto = $this->obtenerProductoPorId($detalle->producto_id);
        $producto->stock -= $diferenciaCantidad;
        $producto->save();
    }


}
