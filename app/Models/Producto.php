<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Esta clase representa a la tabla "producto" en la base de datos

 */
class Producto extends Model
{
    protected $table = 'producto';

    protected $fillable = [
        'nombre', 'precio', 'stock', 'foto', 'categoria_id'
    ];

    use HasFactory;

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /*Relacion con la tabla detalle_pedido */

    public function detallePedido()
    {
        return $this->hasMany(DetallePedido::class, 'producto_id');
    }
}
