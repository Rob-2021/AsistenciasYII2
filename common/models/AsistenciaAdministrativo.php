<?php
namespace common\models;

use yii\db\ActiveRecord;

class AsistenciaAdministrativo extends ActiveRecord
{
    public static function tableName()
    {
        return 'AsistenciaAdministrativos';
    }

    public function rules()
    {
        return [
            [['IdPersona', 'CodigoTipoHorario', 'CodigoTurno', 'HoraEntrada', 'HoraRegistroEntrada', 'HoraMinimaEntrada', 'HoraMaximaEntrada', 'HoraSalida', 'HoraRegistroSalida', 'HoraMinimaSalida', 'HoraMaximaSalida', 'EstadoEntrada', 'EstadoSalida', 'Sanciones', 'EstadoAsistencia', 'Observaciones'], 'safe'],
        ];
    }

    public function getPersona()
    {
        return $this->hasOne(Persona::class, ['IdPersona' => 'IdPersona']);
    }
}
