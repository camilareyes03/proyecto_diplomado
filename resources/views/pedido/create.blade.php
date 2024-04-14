@extends('adminlte::page')

@section('title', 'Crear Pedido')

@section('content_header')
    <h1>Crear Pedido Nuevo</h1>
@stop

@section('content')
    <form action="{{ route('pedidos.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="fecha" class="form-label">Fecha</label>
            <input type="date" id="fecha" name="fecha" class="form-control" tabindex="1">
            @error('fecha')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="tipo_pedido" class="form-label">Tipo de Pedido</label>
            <select id="tipo_pedido" name="tipo_pedido" class="form-control" tabindex="2">
                <option value="proforma">Proforma</option>
                <option value="oficial">Oficial</option>
            </select>
            @error('tipo_pedido')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="cliente_id" class="form-label">Cliente</label>
            <select id="cliente_id" name="cliente_id" class="form-control" tabindex="3">
                <option value="">Seleccionar un cliente</option>
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->name }}</option>
                @endforeach
            </select>
            @error('cliente_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>


        <div class="mb-3" id="tipo_pago_container" style="display: none;">
            <label for="tipo_pago" class="form-label">Tipo de Pago</label>
            <select id="tipo_pago" name="tipo_pago" class="form-control" tabindex="5">
                <option value="qr">QR</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="efectivo">Efectivo</option>
            </select>
            @error('tipo_pago')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <a href="/pedidos" class="btn btn-secondary" tabindex="6">Cancelar</a>
        <button style="background-color: rgb(1, 130, 5); border: 1px solid rgb(1, 130, 5);" type="submit" class="btn btn-primary" tabindex="7">Guardar</button>
    </form>
@stop
@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tipo_pedido').change(function() {
                var selectedOption = $(this).val();
                var tipoPagoContainer = $('#tipo_pago_container');

                if (selectedOption === 'oficial') {
                    tipoPagoContainer.show();
                } else {
                    tipoPagoContainer.hide();
                }
            });
        });
    </script>
@stop
