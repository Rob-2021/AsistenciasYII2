<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'Personas';

    

    protected $fillable = ['IdPersona', 'CodigoLugarEmision', 'Paterno', 'Materno', 'Nombres', 'FechaNacimiento', 'Sexo', 'IdLugarNacimiento', 'CodigoUsuario', 'FechaHoraRegistro', 'Observaciones'];

    protected $casts = [
        'FechaNacimiento' => 'datetime',
        'IdLugarNacimiento' => 'integer',
        'FechaHoraRegistro' => 'datetime'
    ];

    protected $dates = ['FechaNacimiento', 'FechaHoraRegistro'];



}