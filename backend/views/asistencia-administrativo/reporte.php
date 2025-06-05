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
$mesReporte = '';
if (count($asistencias) > 0) {
    $admin = $asistencias[0]->persona;
    if ($admin) {
        $nombreAdministrativo = trim(($admin->Nombres ?? '') . ' ' . ($admin->Paterno ?? '') . ' ' . ($admin->Materno ?? ''));
    }
    // Obtener mes del primer registro
    $mesNum = $asistencias[0]->HoraEntrada ? date('m', strtotime($asistencias[0]->HoraEntrada)) : '';
    $anio = $asistencias[0]->HoraEntrada ? date('Y', strtotime($asistencias[0]->HoraEntrada)) : '';
    $mesesEspanol = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
    ];
    if ($mesNum && $anio) {
        $mesReporte = $mesesEspanol[$mesNum] . ' ' . $anio;
    }
}
?>
<h1><?= Html::encode($this->title) ?><?= $mesReporte ? ' - ' . Html::encode($mesReporte) : '' ?></h1>
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
            <th>Minutos Retraso</th>
            <th>Atrasos</th>
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

        $mes = $asistencia->HoraEntrada ? date('Y-m', strtotime($asistencia->HoraEntrada)) : '';
        $personaId = $asistencia->IdPersona;
        $clave = $personaId . '-' . $mes;
        if (!isset($minutosPorPersonaMes[$clave])) $minutosPorPersonaMes[$clave] = 0;

        if ($asistencia->HoraEntrada && $asistencia->HoraRegistroEntrada) {
            $entradaTime  = strtotime($asistencia->HoraEntrada);
            $registroTime = strtotime($asistencia->HoraRegistroEntrada);
            if ($registroTime > $entradaTime) {
                $segundosRetraso = $registroTime - $entradaTime;
                $minutosRetraso = round($segundosRetraso / 60, 2);
                $minutosPorPersonaMes[$clave] += $minutosRetraso;
            }
        }
        
        $acumuladoMes = isset($minutosPorPersonaMes[$clave]) ? $minutosPorPersonaMes[$clave] : 0;
        $acumuladoHtml = number_format($acumuladoMes, 2);
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
            <td><?= $acumuladoHtml ?></td>
            <td><?= $numAtrasos ?? 0 ?></td>
            <td><?= Html::encode($observaciones) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
