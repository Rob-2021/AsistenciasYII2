<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencias</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; }
        .rojo { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Reporte de Asistencias Administrativos</h2>
    <table>
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
                {{-- <th>Sanciones</th> --}}
                <th>EstadoAsistencia</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @php
                // inicializo el contador de atrasos POR PERSONA Y MES
                $atrasosPorPersonaMes = [];
            @endphp
        
            @foreach ($asistencias as $asistencia)
            @php
                $nombreCompleto = $asistencia->persona->Nombres ?? '' . ' ' . $asistencia->persona->Paterno ?? '' . ' ' . $asistencia->persona->Materno ?? '';
                $retrasoTexto = '';
                $mes = $asistencia->HoraEntrada ? \Carbon\Carbon::parse($asistencia->HoraEntrada)->format('Y-m') : '';
                $personaId = $asistencia->IdPersona;
                $clave = $personaId . '-' . $mes;
            
                if (!isset($minutosPorPersonaMes)) $minutosPorPersonaMes = [];
                if (!isset($reincidenciasPorPersonaMes)) $reincidenciasPorPersonaMes = [];
                if (!isset($atrasosPorPersonaMes)) $atrasosPorPersonaMes = [];
            
                if ($asistencia->HoraEntrada && $asistencia->HoraRegistroEntrada) {
                    $entrada  = \Carbon\Carbon::parse($asistencia->HoraEntrada);
                    $registro = \Carbon\Carbon::parse($asistencia->HoraRegistroEntrada);
            
                    if ($registro->greaterThan($entrada)) {
                        $segundosRetraso = $registro->diffInSeconds($entrada, false);
                        $segundosRetrasoAbs = abs($segundosRetraso);
                        $minutosRetraso = round($segundosRetrasoAbs / 60, 2);
            
                        // Acumula minutos de atraso por persona y mes
                        $minutosPorPersonaMes[$clave] = ($minutosPorPersonaMes[$clave] ?? 0) + $minutosRetraso;
            
                        // 1. Atrasos normales (más de 5 min)
                        if ($segundosRetrasoAbs > 300 && $minutosRetraso < 10) {
                            $atrasosPorPersonaMes[$clave] = ($atrasosPorPersonaMes[$clave] ?? 0) + 1;
                            $numAtrasos = $atrasosPorPersonaMes[$clave];
                            if ($numAtrasos === 4) {
                                $retrasoTexto = "<span class='rojo'>{$numAtrasos} atrasos ({$minutosRetraso} min) - 0.5 día descuento</span>";
                            } elseif ($numAtrasos === 8) {
                                $retrasoTexto = "<span class='rojo'>{$numAtrasos} atrasos ({$minutosRetraso} min) - 1 día descuento</span>";
                            } elseif ($numAtrasos === 12) {
                                $retrasoTexto = "<span class='rojo'>{$numAtrasos} atrasos ({$minutosRetraso} min) - 2 días descuento</span>";
                            } else {
                                $retrasoTexto = "{$numAtrasos} atraso(s) ({$minutosRetraso} min)";
                            }
                        }
                        // 2. Descuentos por atraso individual
                        elseif ($minutosRetraso >= 10 && $minutosRetraso <= 30) {
                            $reincidenciasPorPersonaMes[$clave] = ($reincidenciasPorPersonaMes[$clave] ?? 0) + 1;
                            if ($reincidenciasPorPersonaMes[$clave] > 1) {
                                $retrasoTexto = "<span class='rojo'>Atraso de {$minutosRetraso} min - 2 días descuento (reincidencia)</span>";
                            } else {
                                $retrasoTexto = "<span class='rojo'>Atraso de {$minutosRetraso} min - 0.5 día descuento</span>";
                            }
                        } elseif ($minutosRetraso > 30) {
                            $reincidenciasPorPersonaMes[$clave] = ($reincidenciasPorPersonaMes[$clave] ?? 0) + 1;
                            if ($reincidenciasPorPersonaMes[$clave] > 1) {
                                $retrasoTexto = "<span class='rojo'>Atraso de {$minutosRetraso} min - 2 días descuento (reincidencia)</span>";
                            } else {
                                $retrasoTexto = "<span class='rojo'>Atraso de {$minutosRetraso} min - doble jornada descuento</span>";
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
                        $descuentoMes = " - <span class='rojo'>6 días descuento (acumulado mes)</span>";
                    } elseif ($minutosPorPersonaMes[$clave] > 120) {
                        $descuentoMes = " - <span class='rojo'>8 días descuento (acumulado mes)</span>";
                    }
                }
            @endphp
                <tr>
                    <td>{{ $asistencia->persona->Nombres ?? '' }} {{ $asistencia->persona->Paterno ?? '' }} {{ $asistencia->persona->Materno ?? '' }}</td>
                    <td>{{ $asistencia->CodigoTurno }}</td>
                    <td>{{ $asistencia->HoraEntrada }}</td>
                    <td>{{ $asistencia->HoraRegistroEntrada }}</td>
                    <td>{!! $retrasoTexto . $descuentoMes !!}<br><small>Acumulado mes: <b>{{ isset($minutosPorPersonaMes[$clave]) ? $minutosPorPersonaMes[$clave] : 0 }} min</b></small></td>
                    <td>{{ $asistencia->HoraSalida }}</td>
                    <td>{{ $asistencia->HoraRegistroSalida }}</td>
                    <td>{{ $asistencia->EstadoEntrada }}</td>
                    <td>{{ $asistencia->EstadoSalida }}</td>
                    {{-- <td>{{ $asistencia->Sanciones }}</td> --}}
                    <td>{{ $asistencia->EstadoAsistencia }}</td>
                    <td>{{ $asistencia->Observaciones }}</td>
                </tr>
            @endforeach
        </tbody>
        
    </table>
</body>
</html>