<?php
use yii\helpers\Html;

$this->title = 'Consulta de Asistencia Administrativos';
$id = Yii::$app->request->get('id', '');
$mes = Yii::$app->request->get('mes', '');
$dia = Yii::$app->request->get('dia', '');
$mostrar = $id && ($mes || $dia);
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

<form id="busqueda-form" method="get" action="<?= \yii\helpers\Url::to(['asistencia-administrativo/asistencia-admcomun']) ?>">
    <input type="text" name="id" placeholder="Ingrese su IdPersona..." value="<?= Html::encode($id) ?>">
    <input type="month" name="mes" value="<?= Html::encode($mes) ?>">
    <input type="date" name="dia" value="<?= Html::encode($dia) ?>">
    <button id="buscar-btn" type="submit">Buscar</button>
    <a href="<?= \yii\helpers\Url::to(array_merge(['asistencia-administrativo/exportar', 'tipo' => 'pdf'], $_GET)) ?>" class="btn btn-danger" target="_blank">Generar PDF</a>
    <a href="<?= \yii\helpers\Url::to(array_merge(['asistencia-administrativo/exportar', 'tipo' => 'excel'], $_GET)) ?>" class="btn btn-success" target="_blank">Generar Excel</a>
</form>

<script>
    document.getElementById('busqueda-form').addEventListener('submit', function(e) {
        var btn = document.getElementById('buscar-btn');
        var overlay = document.getElementById('preloader-overlay');
        btn.disabled = true;
        overlay.style.display = 'flex';
    });
</script>

<?php if ($mostrar): ?>
    <?php
    // Renderizar la tabla de resultados reutilizando la vista de reporte
    echo $this->render('reporte', ['asistencias' => isset($asistencias) ? $asistencias : []]);
    ?>
<?php endif; ?>
