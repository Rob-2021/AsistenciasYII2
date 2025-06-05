<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap5\LinkPager;
$this->title = 'Planilla Detallada de Asistencia Administrativa';
?>
<h1><?= Html::encode($this->title) ?></h1>


<?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['asistencia-administrativo/planillas']]); ?>
<?= $form->field($model, 'mes')->input('month', ['value' => $model->mes ?? ''])->label('Mes:') ?>
<button type="submit" class="btn btn-primary">Filtrar</button>

<div class="mb-3">
    <a href="#" class="btn btn-danger" target="_blank">Generar PDF</a>
    <a href="#" class="btn btn-success" target="_blank">Reporte</a>
</div>

<?php ActiveForm::end(); ?>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No.</th>
            <th>CI</th>
            <th>Item</th>
            <th>Nombres</th>
            <th>Faltas Sin/Lic</th>
            <th>Faltas Con/Lic</th>
            <th>Sanc.</th>
            <th>Omisión Registro</th>
            <th>Atrasos</th>
            <th>Med/Falt</th>
            <th>Vac</th>
            <th>Sueldo</th>
        </tr>
    </thead>
    <tbody>
    <?php if (!empty($data)): ?>
        <?php $no = ($pagination->getPage() * $pagination->getPageSize()) + 1; ?>
        <?php foreach ($data as $fila): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= Html::encode($fila['IdPersona']) ?></td>
                <td></td>
                <td>
                    <?php
                    $persona = $fila['persona'];
                    echo $persona ? Html::encode(trim(($persona->Nombres ?? '') . ' ' . ($persona->Paterno ?? '') . ' ' . ($persona->Materno ?? ''))) : '';
                    ?>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="12">No hay datos para mostrar.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<?= isset($pagination) ? LinkPager::widget(['pagination' => $pagination]) : '' ?>
