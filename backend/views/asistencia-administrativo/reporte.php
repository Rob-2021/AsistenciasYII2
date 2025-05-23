<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = 'Reporte de Asistencias';

// Suponiendo que $asistencias es un array de modelos AsistenciaAdministrativo
$asistencias = isset($asistencias) ? $asistencias : [];
$minutosPorPersonaMes = [];
$reincidenciasPorPersonaMes = [];
$atrasosPorPersonaMes = [];

?>
<h1><?= Html::encode($this->title) ?></h1>
<p></p>
<table border="1" cellpadding="4" cellspacing="0" style="width:100%; font-size:12px;">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>CodigoTurno</th>
            <th>HoraEntrada</th>
            <th>HoraRegistroEntrada</th>
            <th>Retraso</th>
            <th>HoraSalida</th>
            <th>HoraRegistroSalida</th>
            <th>EstadoEntrada</th>
            <th>EstadoSalida</th>
            <th>EstadoAsistencia</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($asistencias as $asistencia):
        $nombreCompleto = ($asistencia->persona->Nombres ?? '') . ' ' . ($asistencia->persona->Paterno ?? '') . ' ' . ($asistencia->persona->Materno ?? '');
        $retrasoTexto = '';
        $mes = $asistencia->HoraEntrada ? date('Y-m', strtotime($asistencia->HoraEntrada)) : '';
        $personaId = $asistencia->IdPersona;
        $clave = $personaId . '-' . $mes;

        if (!isset($minutosPorPersonaMes[$clave])) $minutosPorPersonaMes[$clave] = 0;
        if (!isset($reincidenciasPorPersonaMes[$clave])) $reincidenciasPorPersonaMes[$clave] = 0;
        if (!isset($atrasosPorPersonaMes[$clave])) $atrasosPorPersonaMes[$clave] = 0;
        
        if ($asistencia->HoraEntrada && $asistencia->HoraRegistroEntrada) {
            $entrada  = strtotime($asistencia->HoraEntrada);
            $registro = strtotime($asistencia->HoraRegistroEntrada);
            
            if ($registro > $entrada) {
                $segundosRetraso = $registro - $entrada;
                $minutosRetraso = round($segundosRetraso / 60, 2);
                $minutosPorPersonaMes[$clave] += $minutosRetraso;
                // 1. Atrasos normales (más de 5 min)
                if ($segundosRetraso > 300 && $minutosRetraso < 10) {
                    $atrasosPorPersonaMes[$clave]++;
                    $numAtrasos = $atrasosPorPersonaMes[$clave];
                    if ($numAtrasos === 4) {
                        $retrasoTexto = "<span style='color:red;font-weight:bold'>{$numAtrasos} atrasos ({$minutosRetraso} min) - 0.5 día descuento</span>";
                    } elseif ($numAtrasos === 8) {
                        $retrasoTexto = "<span style='color:red;font-weight:bold'>{$numAtrasos} atrasos ({$minutosRetraso} min) - 1 día descuento</span>";
                    } elseif ($numAtrasos === 12) {
                        $retrasoTexto = "<span style='color:red;font-weight:bold'>{$numAtrasos} atrasos ({$minutosRetraso} min) - 2 días descuento</span>";
                    } else {
                        $retrasoTexto = "{$numAtrasos} atraso(s) ({$minutosRetraso} min)";
                    }
                }
                // 2. Descuentos por atraso individual
                elseif ($minutosRetraso >= 10 && $minutosRetraso <= 30) {
                    $reincidenciasPorPersonaMes[$clave]++;
                    if ($reincidenciasPorPersonaMes[$clave] > 1) {
                        $retrasoTexto = "<span style='color:red;font-weight:bold'>Atraso de {$minutosRetraso} min - 2 días descuento (reincidencia)</span>";
                    } else {
                        $retrasoTexto = "<span style='color:red;font-weight:bold'>Atraso de {$minutosRetraso} min - 0.5 día descuento</span>";
                    }
                } elseif ($minutosRetraso > 30) {
                    $reincidenciasPorPersonaMes[$clave]++;
                    if ($reincidenciasPorPersonaMes[$clave] > 1) {
                        $retrasoTexto = "<span style='color:red;font-weight:bold'>Atraso de {$minutosRetraso} min - 2 días descuento (reincidencia)</span>";
                    } else {
                        $retrasoTexto = "<span style='color:red;font-weight:bold'>Atraso de {$minutosRetraso} min - doble jornada descuento</span>";
                    }
                } else {
                    $retrasoTexto = "{$minutosRetraso} min";
                }
            }
        }
        // Descuentos por acumulado mensual
        $descuentoMes = '';
        if (isset($minutosPorPersonaMes[$clave])) {
            if ($minutosPorPersonaMes[$clave] >= 100 && $minutosPorPersonaMes[$clave] <= 120) {
                $descuentoMes = " - <span style='color:red;font-weight:bold'>6 días descuento (acumulado mes)</span>";
            } elseif ($minutosPorPersonaMes[$clave] > 120) {
                $descuentoMes = " - <span style='color:red;font-weight:bold'>8 días descuento (acumulado mes)</span>";
            }
        }
    ?>
        <tr>
            <td><?= Html::encode($nombreCompleto) ?></td>
            <td><?= Html::encode($asistencia->CodigoTurno) ?></td>
            <td><?= Html::encode($asistencia->HoraEntrada) ?></td>
            <td><?= Html::encode($asistencia->HoraRegistroEntrada) ?></td>
            <td><?= $retrasoTexto . $descuentoMes ?><br><small>Acumulado mes: <b><?= isset($minutosPorPersonaMes[$clave]) ? $minutosPorPersonaMes[$clave] : 0 ?> min</b></small></td>
            <td><?= Html::encode($asistencia->HoraSalida) ?></td>
            <td><?= Html::encode($asistencia->HoraRegistroSalida) ?></td>
            <td><?= Html::encode($asistencia->EstadoEntrada) ?></td>
            <td><?= Html::encode($asistencia->EstadoSalida) ?></td>
            <td><?= Html::encode($asistencia->EstadoAsistencia) ?></td>
            <td><?= Html::encode($asistencia->Observaciones) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
