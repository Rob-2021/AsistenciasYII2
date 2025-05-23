<?php

namespace App\Exports;

use App\Models\AsistenciaAdministrativo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AsistenciasExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = AsistenciaAdministrativo::with('persona')
            ->orderBy('IdPersona', 'desc');

        // Filtros de búsqueda
        if ($this->request->filled('busqueda')) {
            $busqueda = $this->request->input('busqueda');
            $palabras = preg_split('/\s+/', trim($busqueda));
            $query->whereHas('persona', function ($q) use ($palabras) {
                foreach ($palabras as $palabra) {
                    $q->where(function ($subq) use ($palabra) {
                        $subq->where('Nombres', 'like', "%$palabra%")
                            ->orWhere('Paterno', 'like', "%$palabra%")
                            ->orWhere('Materno', 'like', "%$palabra%");
                    });
                }
            });
        }
        if ($this->request->filled('dia')) {
            $query->whereDate('HoraEntrada', $this->request->input('dia'));
        }
        if ($this->request->filled('mes')) {
            [$anio, $mes] = explode('-', $this->request->input('mes'));
            $query->whereYear('HoraEntrada', $anio)
                ->whereMonth('HoraEntrada', $mes);
        }
        if ($this->request->filled('anio')) {
            $query->whereYear('HoraEntrada', $this->request->input('anio'));
        }
        if ($this->request->filled('codigo_turno')) {
            $query->where('CodigoTurno', $this->request->input('codigo_turno'));
        }

        $asistencias = $query->get();

        // Calcular atrasos acumulados por persona y mes

        $atrasosPorPersonaMes = [];
        $minutosPorPersonaMes = [];
        $reincidenciasPorPersonaMes = [];
        $result = [];

        foreach ($asistencias as $asistencia) {
            $nombreCompleto = $asistencia->persona
                ? $asistencia->persona->Nombres . ' ' . $asistencia->persona->Paterno . ' ' . $asistencia->persona->Materno
                : '';
            $retrasoTexto = '';
            $mes = $asistencia->HoraEntrada ? \Carbon\Carbon::parse($asistencia->HoraEntrada)->format('Y-m') : '';
            $personaId = $asistencia->IdPersona;
            $clave = $personaId . '-' . $mes;

            if ($asistencia->HoraEntrada && $asistencia->HoraRegistroEntrada) {
                $entrada  = \Carbon\Carbon::parse($asistencia->HoraEntrada);
                $registro = \Carbon\Carbon::parse($asistencia->HoraRegistroEntrada);

                if ($registro->greaterThan($entrada)) {
                    $segundosRetraso = $registro->diffInSeconds($entrada, false);
                    $segundosRetrasoAbs = abs($segundosRetraso);
                    $minutosRetraso = round($segundosRetrasoAbs / 60, 2);

                    // Acumula minutos de atraso por persona y mes
                    $minutosPorPersonaMes[$clave] = ($minutosPorPersonaMes[$clave] ?? 0) + $minutosRetraso;

                    // 1. Atrasos normales (más de 5 min y menos de 10)
                    if ($segundosRetrasoAbs > 300 && $minutosRetraso < 10) {
                        $atrasosPorPersonaMes[$clave] = ($atrasosPorPersonaMes[$clave] ?? 0) + 1;
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
                    }
                    // 2. Descuentos por atraso individual
                    elseif ($minutosRetraso >= 10 && $minutosRetraso <= 30) {
                        $reincidenciasPorPersonaMes[$clave] = ($reincidenciasPorPersonaMes[$clave] ?? 0) + 1;
                        if ($reincidenciasPorPersonaMes[$clave] > 1) {
                            $retrasoTexto = "Atraso de {$minutosRetraso} min - 2 días descuento (reincidencia)";
                        } else {
                            $retrasoTexto = "Atraso de {$minutosRetraso} min - 0.5 día descuento";
                        }
                    } elseif ($minutosRetraso > 30) {
                        $reincidenciasPorPersonaMes[$clave] = ($reincidenciasPorPersonaMes[$clave] ?? 0) + 1;
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

            // Descuentos por acumulado mensual
            $descuentoMes = '';
            $acumuladoMes = isset($minutosPorPersonaMes[$clave]) ? $minutosPorPersonaMes[$clave] : 0;
            if ($acumuladoMes >= 100 && $acumuladoMes <= 120) {
                $descuentoMes = " - 6 días descuento (acumulado mes)";
            } elseif ($acumuladoMes > 120) {
                $descuentoMes = " - 8 días descuento (acumulado mes)";
            }

            $result[] = [
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
            ];
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'CodigoTipoHorario',
            'CodigoTurno',
            'HoraEntrada',
            'HoraRegistroEntrada',
            'Retraso',
            'HoraSalida',
            'HoraRegistroSalida',
            'EstadoEntrada',
            'EstadoSalida',
            'Sanciones',
            'EstadoAsistencia',
            'Observaciones',
        ];
    }
}
