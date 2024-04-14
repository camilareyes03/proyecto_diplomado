<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Esta clase representa la tabla categoria
 */
class Categoria extends Model
{
    protected $table = 'categoria';

    protected $fillable = [
        'nombre', 'descripcion'
    ];

    use HasFactory;

    /*Relacion con la tabla producto */
    public function producto(){
        return $this->hasMany(Producto::class,'categoria_id');
    }
}
