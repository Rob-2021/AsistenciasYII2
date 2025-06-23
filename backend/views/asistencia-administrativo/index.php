<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;

$this->title = 'Asistencias de Administrativos';
?>
<h1><?= Html::encode($this->title) ?></h1>

<!-- Overlay Preloader centrado -->
<div id="preloader-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; justify-content:center; align-items:center;">
    <div>
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <div style="color:white; text-align:center; margin-top:10px; font-size:1.2rem;">Cargando...</div>
    </div>
</div>

<form id="busqueda-form" method="get" action="<?= \yii\helpers\Url::to(['asistencia-administrativo/index']) ?>">
    <input type="text" name="busqueda" placeholder="Buscar por IdPersona..." value="<?= Html::encode(Yii::$app->request->get('busqueda', '')) ?>">
    <input type="month" name="mes" value="<?= Html::encode(Yii::$app->request->get('mes', '')) ?>">
    <input type="date" name="dia" value="<?= Html::encode(Yii::$app->request->get('dia', '')) ?>">
    <button id="buscar-btn" type="submit">Buscar</button>
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
            'value' => function ($model) {
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
    'pager' => [
        'class' => 'yii\\bootstrap5\\LinkPager',
    ],
]); ?>

<script>
    document.getElementById('busqueda-form').addEventListener('submit', function(e) {
        var btn = document.getElementById('buscar-btn');
        var overlay = document.getElementById('preloader-overlay');
        btn.disabled = true;
        overlay.style.display = 'flex';
    });
</script>