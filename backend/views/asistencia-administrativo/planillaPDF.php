<?php
use yii\helpers\Html;

?>
<h2 style="text-align:center;">Planilla Detallada de Asistencia Administrativa</h2>
<?php if ($mes): ?>
    <p><strong>Mes:</strong> <?= Html::encode($mes) ?></p>
<?php endif; ?>
<table border="1" cellpadding="4" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:12px;">
    <thead style="background:#f2f2f2;">
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
        <?php $no = 1; ?>
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
