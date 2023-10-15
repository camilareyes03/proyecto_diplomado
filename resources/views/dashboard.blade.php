@extends('adminlte::page')
@section('content')
<div class="message-box" style="position: absolute; top: 20%; left: 29%; transform: translate(-50%, -50%); background-color: rgba(213, 210, 210, 0.8); padding: 1%; border: 2px solid #000;">
    <h1>Bienvenido!</h1>
</div>


    <video autoplay loop muted plays-inline class="back-video" id="background-video" style="width: 50%; height: 50%; position: absolute; top: 55%; left: 41%; transform: translate(-50%, -50%);">
        <source src="{{ asset('welcome/vid/video2.mp4') }}" type="video/mp4">
    </video>

    <div class="card" style="width: 18rem; position: absolute; top: 32%; left: 70%;">
        <img class="card-img-top" src="{{ asset('welcome/img/logo3.jpg') }}" alt="Card image cap">
        <div class="card-body">
          <p class="card-text">Gestiona todo tu comercio con este software!
            <br>
            Contacto: JoseElCrack@hotmail.com
            <br>
            Psdt:Muy Crack
          </p>
        </div>
      </div>

@stop