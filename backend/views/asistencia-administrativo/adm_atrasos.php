<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Administrativos con más atrasos y minutos acumulados';
?>
<h1><?= Html::encode($this->title) ?></h1>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'mes')->input('month') ?>
    <button type="submit" class="btn btn-primary">Buscar</button>
<?php ActiveForm::end(); ?>

<?php if ($data): ?>
    <h3>Resultados para el mes seleccionado</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Minutos acumulados</th>
                <th>Cantidad de atrasos</th>
                <th>Reporte</th>
            </tr>
        </thead>
        <tbody>
        <?php $i=1; foreach ($data as $item): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= Html::encode(($item['persona']->Nombres ?? '') . ' ' . ($item['persona']->Paterno ?? '') . ' ' . ($item['persona']->Materno ?? '')) ?></td>
                <td><?= Html::encode($item['minutos']) ?></td>
                <td><?= Html::encode($item['atrasos']) ?></td>
                <td>
                    <?php $mes = $model->mes; ?>
                    <a href="<?= \yii\helpers\Url::to(['asistencia-administrativo/exportar', 'tipo' => 'pdf', 'mes' => $mes, 'busqueda' => $item['persona']->Nombres]) ?>" class="btn btn-danger btn-sm" target="_blank">PDF</a>
                    <a href="<?= \yii\helpers\Url::to(['asistencia-administrativo/exportar', 'tipo' => 'excel', 'mes' => $mes, 'busqueda' => $item['persona']->Nombres]) ?>" class="btn btn-success btn-sm" target="_blank">Excel</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
