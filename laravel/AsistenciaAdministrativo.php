<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaAdministrativo extends Model
{
    protected $table = 'AsistenciaAdministrativos';

    // Clave primaria compuesta: ['CodigoTipoHorario', 'CodigoTurno', 'HoraEntrada', 'IdPersona']

    protected $fillable = ['IdPersona', 'CodigoTipoHorario', 'CodigoTurno', 'HoraEntrada', 'HoraRegistroEntrada', 'HoraMinimaEntrada', 'HoraMaximaEntrada', 'HoraSalida', 'HoraRegistroSalida', 'HoraMinimaSalida', 'HoraMaximaSalida', 'EstadoEntrada', 'EstadoSalida', 'Sanciones', 'EstadoAsistencia', 'Observaciones'];

    protected $casts = [
        'HoraEntrada' => 'datetime',
        'HoraRegistroEntrada' => 'datetime',
        'HoraMinimaEntrada' => 'datetime',
        'HoraMaximaEntrada' => 'datetime',
        'HoraSalida' => 'datetime',
        'HoraRegistroSalida' => 'datetime',
        'HoraMinimaSalida' => 'datetime',
        'HoraMaximaSalida' => 'datetime'
    ];

    protected $dates = ['HoraEntrada', 'HoraRegistroEntrada', 'HoraMinimaEntrada', 'HoraMaximaEntrada', 'HoraSalida', 'HoraRegistroSalida', 'HoraMinimaSalida', 'HoraMaximaSalida'];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'IdPersona', 'IdPersona');
    }

    public function personas()
    {
        return $this->hasMany(Persona::class, 'IdPersona');
    }

}