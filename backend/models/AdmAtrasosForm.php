<?php
namespace backend\models;

use yii\base\Model;

class AdmAtrasosForm extends Model
{
    public $mes;
    public function rules()
    {
        return [
            [['mes'], 'required'],
            [['mes'], 'match', 'pattern' => '/^\d{4}-\d{2}$/'],
        ];
    }
}
