<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Administrativos con más atrasos y minutos acumulados';
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

<?php $form = ActiveForm::begin(['id' => 'adm-atrasos-form']); ?>
    <?= $form->field($model, 'mes')->input('month') ?>
    <button type="submit" class="btn btn-primary" id="buscar-btn">Buscar</button>
<?php ActiveForm::end(); ?>

<script>
    var form = document.getElementById('adm-atrasos-form');
    var btn = document.getElementById('buscar-btn');
    var overlay = document.getElementById('preloader-overlay');

    // Mostrar preloader al enviar
    form.addEventListener('submit', function(e) {
        btn.disabled = true;
        overlay.style.display = 'flex';
    });

    // Ocultar preloader cuando termine el submit AJAX de Yii2
    if (window.jQuery) {
        $(form).on('ajaxComplete', function() {
            btn.disabled = false;
            overlay.style.display = 'none';
        });
        $(form).on('ajaxError', function() {
            btn.disabled = false;
            overlay.style.display = 'none';
        });
    }
</script>

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
                    <a href="<?= \yii\helpers\Url::to(['asistencia-administrativo/exportar', 'tipo' => 'pdf', 'mes' => $mes, 'id' => $item['IdPersona']]) ?>" class="btn btn-danger btn-sm" target="_blank">PDF</a>
                    <a href="<?= \yii\helpers\Url::to(['asistencia-administrativo/exportar', 'tipo' => 'excel', 'mes' => $mes, 'id' => $item['IdPersona']]) ?>" class="btn btn-success btn-sm" target="_blank">Excel</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
