<?php
use yii\helpers\Html;

$this->title = 'Consulta de Asistencia Administrativos';
$id = Yii::$app->request->get('id', '');
$mes = Yii::$app->request->get('mes', '');
$dia = Yii::$app->request->get('dia', '');
$mostrar = $id && ($mes || $dia);
?>
<h1><?= Html::encode($this->title) ?></h1>

<form method="get" action="<?= \yii\helpers\Url::to(['asistencia-administrativo/asistencia-admcomun']) ?>">
    <input type="text" name="id" placeholder="Ingrese su IdPersona..." value="<?= Html::encode($id) ?>">
    <input type="month" name="mes" value="<?= Html::encode($mes) ?>">
    <input type="date" name="dia" value="<?= Html::encode($dia) ?>">
    <button type="submit">Buscar</button>
    <a href="<?= \yii\helpers\Url::to(array_merge(['asistencia-administrativo/exportar', 'tipo' => 'pdf'], $_GET)) ?>" class="btn btn-danger" target="_blank">Generar PDF</a>
    <a href="<?= \yii\helpers\Url::to(array_merge(['asistencia-administrativo/exportar', 'tipo' => 'excel'], $_GET)) ?>" class="btn btn-success" target="_blank">Generar Excel</a>
</form>

<?php if ($mostrar): ?>
    <?php
    // Renderizar la tabla de resultados reutilizando la vista de reporte
    echo $this->render('reporte', ['asistencias' => isset($asistencias) ? $asistencias : []]);
    ?>
<?php endif; ?>
