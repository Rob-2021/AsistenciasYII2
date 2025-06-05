<?php
namespace backend\models;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReporteExcel
{
    public static function generar($asistencias)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
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
        $sheet->fromArray([
            ['Nombre', 'Fecha', 'Día', 'CodigoTipoHorario', 'CodigoTurno', 'HoraEntrada', 'HoraRegistroEntrada', 'HoraSalida', 'HoraRegistroSalida', 'EstadoEntrada', 'EstadoSalida', 'EstadoAsistencia', 'Retraso', 'Atrasos', 'Observaciones']
        ], null, 'A1');

        // Ordenar asistencias por fecha y luego por turno (M antes que T)
        if (!empty($asistencias)) {
            usort($asistencias, function($a, $b) {
                $fechaA = $a->HoraEntrada ? strtotime($a->HoraEntrada) : 0;
                $fechaB = $b->HoraEntrada ? strtotime($b->HoraEntrada) : 0;
                if ($fechaA === $fechaB) {
                    $turnoA = $a->CodigoTurno ?? '';
                    $turnoB = $b->CodigoTurno ?? '';
                    return strcmp($turnoA, $turnoB);
                }
                return $fechaA <=> $fechaB;
            });
        }

        $minutosPorPersonaMes = [];
        $atrasosAcumuladosPorClave = [];
        $row = 2;
        foreach ($asistencias as $asistencia) {
            $nombreCompleto = ($asistencia->persona->Nombres ?? '') . ' ' . ($asistencia->persona->Paterno ?? '') . ' ' . ($asistencia->persona->Materno ?? '');
            $fecha = $asistencia->HoraEntrada ? date('d/m/Y', strtotime($asistencia->HoraEntrada)) : '';
            $diaEn = $asistencia->HoraEntrada ? date('D', strtotime($asistencia->HoraEntrada)) : '';
            $dia = isset($diasEspanol[$diaEn]) ? $diasEspanol[$diaEn] : $diaEn;
            $mes = $asistencia->HoraEntrada ? date('Y-m', strtotime($asistencia->HoraEntrada)) : '';
            $personaId = $asistencia->IdPersona;
            $clave = $personaId . '-' . $mes;
            if (!isset($minutosPorPersonaMes[$clave])) $minutosPorPersonaMes[$clave] = 0;
            if (!isset($atrasosAcumuladosPorClave[$clave])) $atrasosAcumuladosPorClave[$clave] = 0;
            if ($asistencia->HoraEntrada && $asistencia->HoraRegistroEntrada) {
                $entrada  = strtotime($asistencia->HoraEntrada);
                $registro = strtotime($asistencia->HoraRegistroEntrada);
                if ($registro > $entrada) {
                    $segundosRetraso = $registro - $entrada;
                    $minutosRetraso = round($segundosRetraso / 60, 2);
                    $minutosPorPersonaMes[$clave] += $minutosRetraso;
                }
            }
            // Acumulado progresivo de atrasos (EstadoEntrada == 'AT')
            if (($asistencia->EstadoEntrada ?? '') === 'AT') {
                $atrasosAcumuladosPorClave[$clave]++;
            }
            $acumuladoMes = $minutosPorPersonaMes[$clave];
            $numAtrasos = $atrasosAcumuladosPorClave[$clave];
            $acumuladoHtml = number_format($acumuladoMes, 2);
            $sheet->fromArray([
                $nombreCompleto,
                $fecha,
                $dia,
                $asistencia->CodigoTipoHorario,
                $asistencia->CodigoTurno,
                $asistencia->HoraEntrada,
                $asistencia->HoraRegistroEntrada,
                $asistencia->HoraSalida,
                $asistencia->HoraRegistroSalida,
                $asistencia->EstadoEntrada,
                $asistencia->EstadoSalida,
                $asistencia->EstadoAsistencia,
                $acumuladoHtml,
                $numAtrasos,
                $asistencia->Observaciones,
            ], null, 'A' . $row);
            $row++;
        }
        return $spreadsheet;
    }
}
