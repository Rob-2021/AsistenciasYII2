<?php
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Asistencias de Administrativos';
?>
<h1><?= Html::encode($this->title) ?></h1>

<form method="get" action="<?= \yii\helpers\Url::to(['asistencia-administrativo/index']) ?>">
    <input type="text" name="busqueda" placeholder="Buscar por IdPersona..." value="<?= Html::encode(Yii::$app->request->get('busqueda', '')) ?>">
    <input type="month" name="mes" value="<?= Html::encode(Yii::$app->request->get('mes', '')) ?>">
    <input type="date" name="dia" value="<?= Html::encode(Yii::$app->request->get('dia', '')) ?>">
    <button type="submit">Buscar</button>
    <?php
    // Generar los parámetros actuales para los enlaces de exportación
    $params = Yii::$app->request->get();
    ?>
    <a href="<?= \yii\helpers\Url::to(array_merge(['asistencia-administrativo/exportar', 'tipo' => 'pdf'], $params)) ?>" class="btn btn-danger" target="_blank">Generar PDF</a>
    <a href="<?= \yii\helpers\Url::to(array_merge(['asistencia-administrativo/exportar', 'tipo' => 'excel'], $params)) ?>" class="btn btn-success" target="_blank">Generar Excel</a>
    <a href="<?= \yii\helpers\Url::to(['asistencia-administrativo/adm-atrasos']) ?>" class="btn btn-warning">Adm Atrasos</a>
</form>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'persona',
            'label' => 'Nombre',
            'value' => function($model) {
                return $model->persona ? $model->persona->Nombres . ' ' . $model->persona->Paterno . ' ' . $model->persona->Materno : '';
            }
        ],
        'HoraEntrada',
        'HoraRegistroEntrada',
        'HoraSalida',
        'HoraRegistroSalida',
        'EstadoEntrada',
        'EstadoSalida',
        'Sanciones',
        'EstadoAsistencia',
        'Observaciones',
    ],
]); ?>
