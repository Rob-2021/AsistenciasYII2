<?php
namespace common\models;

use yii\db\ActiveRecord;

class Persona extends ActiveRecord
{
    public static function tableName()
    {
        return 'Personas';
    }

    public function rules()
    {
        return [
            [['IdPersona', 'CodigoLugarEmision', 'Paterno', 'Materno', 'Nombres', 'FechaNacimiento', 'Sexo', 'IdLugarNacimiento', 'CodigoUsuario', 'FechaHoraRegistro', 'Observaciones'], 'safe'],
        ];
    }
}
