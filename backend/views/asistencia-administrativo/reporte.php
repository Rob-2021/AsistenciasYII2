<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

$this->title = 'Reporte de Asistencias';

// Suponiendo que $asistencias es un array de modelos AsistenciaAdministrativo
$asistencias = isset($asistencias) ? $asistencias : [];
$minutosPorPersonaMes = [];
$reincidenciasPorPersonaMes = [];
$atrasosPorPersonaMes = [];

// Ordenar asistencias por fecha y luego por turno (M antes que T)
if (!empty($asistencias)) {
    usort($asistencias, function($a, $b) {
        $fechaA = $a->HoraEntrada ? strtotime($a->HoraEntrada) : 0;
        $fechaB = $b->HoraEntrada ? strtotime($b->HoraEntrada) : 0;
        if ($fechaA === $fechaB) {
            // Ordenar turnos: M antes que T
            $turnoA = $a->CodigoTurno ?? '';
            $turnoB = $b->CodigoTurno ?? '';
            return strcmp($turnoA, $turnoB);
        }
        return $fechaA <=> $fechaB;
    });
}

// Traducción de días a español
$diasEspanol = [
    'Mon' => 'Lunes',
    'Tue' => 'Martes',
    'Wed' => 'Miércoles',
    'Thu' => 'Jueves',
    'Fri' => 'Viernes',
    'Sat' => 'Sábado',
    'Sun' => 'Domingo',
];

// Mostrar el nombre del administrativo si solo hay uno en el reporte
$nombreAdministrativo = '';
if (count($asistencias) > 0) {
    $admin = $asistencias[0]->persona;
    if ($admin) {
        $nombreAdministrativo = trim(($admin->Nombres ?? '') . ' ' . ($admin->Paterno ?? '') . ' ' . ($admin->Materno ?? ''));
    }
}
?>
<h1><?= Html::encode($this->title) ?></h1>
<?php if ($nombreAdministrativo): ?>
    <h3><?= Html::encode($nombreAdministrativo) ?></h3>
<?php endif; ?>
<p></p>
<table border="1" cellpadding="4" cellspacing="0" style="width:100%; font-size:12px;">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Día</th>
            <th>Tipo</th>
            <th>Turno</th>
            <th>Entrada</th>
            <th>Salida</th>
            <th>Registro Entrada</th>
            <th>Registro Salida</th>
            <th>Estado Entrada</th>
            <th>Estado Salida</th>
            <th>Asistencia</th>
            <th>Retraso</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($asistencias as $asistencia):
        $fecha = $asistencia->HoraEntrada ? date('d/m/Y', strtotime($asistencia->HoraEntrada)) : '';
        $diaEn = $asistencia->HoraEntrada ? date('D', strtotime($asistencia->HoraEntrada)) : '';
        $dia = isset($diasEspanol[$diaEn]) ? $diasEspanol[$diaEn] : $diaEn;
        $tipo = $asistencia->CodigoTipoHorario ?? '';
        $turno = $asistencia->CodigoTurno ?? '';
        $entrada = $asistencia->HoraEntrada ? date('H:i', strtotime($asistencia->HoraEntrada)) : '';
        $salida = $asistencia->HoraSalida ? date('H:i', strtotime($asistencia->HoraSalida)) : '';
        $registroEntrada = $asistencia->HoraRegistroEntrada ? date('H:i', strtotime($asistencia->HoraRegistroEntrada)) : '';
        $registroSalida = $asistencia->HoraRegistroSalida ? date('H:i', strtotime($asistencia->HoraRegistroSalida)) : '';
        $estadoEntrada = $asistencia->EstadoEntrada ?? '';
        $estadoSalida = $asistencia->EstadoSalida ?? '';
        $asistenciaEstado = $asistencia->EstadoAsistencia ?? '';
        $observaciones = $asistencia->Observaciones ?? '';
        // Calcular retraso como antes
        $retrasoTexto = '';
        $mes = $asistencia->HoraEntrada ? date('Y-m', strtotime($asistencia->HoraEntrada)) : '';
        $personaId = $asistencia->IdPersona;
        $clave = $personaId . '-' . $mes;
        if (!isset($minutosPorPersonaMes[$clave])) $minutosPorPersonaMes[$clave] = 0;
        if (!isset($reincidenciasPorPersonaMes[$clave])) $reincidenciasPorPersonaMes[$clave] = 0;
        if (!isset($atrasosPorPersonaMes[$clave])) $atrasosPorPersonaMes[$clave] = 0;
        if ($asistencia->HoraEntrada && $asistencia->HoraRegistroEntrada) {
            $entradaTime  = strtotime($asistencia->HoraEntrada);
            $registroTime = strtotime($asistencia->HoraRegistroEntrada);
            if ($registroTime > $entradaTime) {
                $segundosRetraso = $registroTime - $entradaTime;
                $minutosRetraso = round($segundosRetraso / 60, 2);
                $minutosPorPersonaMes[$clave] += $minutosRetraso;
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
                } elseif ($minutosRetraso >= 10 && $minutosRetraso <= 30) {
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
        $acumuladoMes = isset($minutosPorPersonaMes[$clave]) ? $minutosPorPersonaMes[$clave] : 0;
        $acumuladoHtml = "<br><span style='font-weight:bold'>Acumulado mes: ".number_format($acumuladoMes, 2)." min</span>";
        $descuentoMes = '';
        if ($acumuladoMes >= 100 && $acumuladoMes <= 120) {
            $descuentoMes = "<br>- <span class='rojo'>6 días descuento (acumulado mes)</span>";
        } elseif ($acumuladoMes > 120) {
            $descuentoMes = "<br>- <span class='rojo'>8 días descuento (acumulado mes)</span>";
        }
    ?>
        <tr>
            <td><?= Html::encode($fecha) ?></td>
            <td><?= Html::encode($dia) ?></td>
            <td><?= Html::encode($tipo) ?></td>
            <td><?= Html::encode($turno) ?></td>
            <td><?= Html::encode($entrada) ?></td>
            <td><?= Html::encode($salida) ?></td>
            <td><?= Html::encode($registroEntrada) ?></td>
            <td><?= Html::encode($registroSalida) ?></td>
            <td><?= Html::encode($estadoEntrada) ?></td>
            <td><?= Html::encode($estadoSalida) ?></td>
            <td><?= Html::encode($asistenciaEstado) ?></td>
            <td><?= $retrasoTexto . $acumuladoHtml . $descuentoMes ?></td>
            <td><?= Html::encode($observaciones) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
