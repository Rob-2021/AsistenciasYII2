<?php
namespace backend\models;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ReporteExcel
{
    public static function generar($asistencias)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Nombre', 'CodigoTipoHorario', 'CodigoTurno', 'HoraEntrada', 'HoraRegistroEntrada', 'Retraso', 'HoraSalida', 'HoraRegistroSalida', 'EstadoEntrada', 'EstadoSalida', 'Sanciones', 'EstadoAsistencia', 'Observaciones']
        ], null, 'A1');

        $atrasosPorPersonaMes = [];
        $minutosPorPersonaMes = [];
        $reincidenciasPorPersonaMes = [];
        $row = 2;
        foreach ($asistencias as $asistencia) {
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
                    if ($segundosRetraso > 300 && $minutosRetraso < 10) {
                        $atrasosPorPersonaMes[$clave]++;
                        $numAtrasos = $atrasosPorPersonaMes[$clave];
                        if ($numAtrasos === 4) {
                            $retrasoTexto = "{$numAtrasos} atrasos ({$minutosRetraso} min) - 0.5 día descuento";
                        } elseif ($numAtrasos === 8) {
                            $retrasoTexto = "{$numAtrasos} atrasos ({$minutosRetraso} min) - 1 día descuento";
                        } elseif ($numAtrasos === 12) {
                            $retrasoTexto = "{$numAtrasos} atrasos ({$minutosRetraso} min) - 2 días descuento";
                        } else {
                            $retrasoTexto = "{$numAtrasos} atraso(s) ({$minutosRetraso} min)";
                        }
                    } elseif ($minutosRetraso >= 10 && $minutosRetraso <= 30) {
                        $reincidenciasPorPersonaMes[$clave]++;
                        if ($reincidenciasPorPersonaMes[$clave] > 1) {
                            $retrasoTexto = "Atraso de {$minutosRetraso} min - 2 días descuento (reincidencia)";
                        } else {
                            $retrasoTexto = "Atraso de {$minutosRetraso} min - 0.5 día descuento";
                        }
                    } elseif ($minutosRetraso > 30) {
                        $reincidenciasPorPersonaMes[$clave]++;
                        if ($reincidenciasPorPersonaMes[$clave] > 1) {
                            $retrasoTexto = "Atraso de {$minutosRetraso} min - 2 días descuento (reincidencia)";
                        } else {
                            $retrasoTexto = "Atraso de {$minutosRetraso} min - doble jornada descuento";
                        }
                    } else {
                        $retrasoTexto = "{$minutosRetraso} min";
                    }
                }
            }
            $descuentoMes = '';
            $acumuladoMes = $minutosPorPersonaMes[$clave];
            if ($acumuladoMes >= 100 && $acumuladoMes <= 120) {
                $descuentoMes = " - 6 días descuento (acumulado mes)";
            } elseif ($acumuladoMes > 120) {
                $descuentoMes = " - 8 días descuento (acumulado mes)";
            }
            $sheet->fromArray([
                $nombreCompleto,
                $asistencia->CodigoTipoHorario,
                $asistencia->CodigoTurno,
                $asistencia->HoraEntrada,
                $asistencia->HoraRegistroEntrada,
                $retrasoTexto . $descuentoMes . "\nAcumulado mes: {$acumuladoMes} min",
                $asistencia->HoraSalida,
                $asistencia->HoraRegistroSalida,
                $asistencia->EstadoEntrada,
                $asistencia->EstadoSalida,
                $asistencia->Sanciones,
                $asistencia->EstadoAsistencia,
                $asistencia->Observaciones,
            ], null, 'A' . $row);
            $row++;
        }
        return $spreadsheet;
    }
}
