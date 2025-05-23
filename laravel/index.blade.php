<x-layouts.public>

    <div class="flex justify-between items-center mb-4">
        <form method="GET" action="{{ route('asistencia.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="busqueda" value="{{ request('busqueda') }}" placeholder="Buscar por nombre..." class="border rounded px-2 py-1">
            <input type="month" name="mes" value="{{ request('mes') }}" class="border rounded px-2 py-1">
            <input type="date" name="dia" value="{{ request('dia') }}" class="border rounded px-2 py-1">
            {{-- <input type="number" name="anio" value="{{ request('anio') }}" placeholder="Año" min="2000" max="2100" class="border rounded px-2 py-1" style="width: 90px;"> --}}
            <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Buscar</button>

            <a href="{{ route('asistencia.reporte.vista', request()->all()) }}" target="_blank" class="bg-yellow-600 text-white px-3 py-1 rounded">Vista previa PDF</a>
            <a href="{{ route('asistencia.reporte', request()->all()) }}" target="_blank" class="bg-green-600 text-white px-3 py-1 rounded">Generar PDF 1000 Max</a>
            <a href="{{ route('asistencia.excel', request()->all()) }}" class="bg-green-700 text-white px-3 py-1 rounded" target="_blank">Exportar Excel</a>
        </form>
        <span id="hora-sistema" class="text-lg font-semibold text-gray-700"></span>
    </div>

    <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Nombre
                    </th>
                    <th scope="col" class="px-6 py-3">
                        CodigoTipoHorario
                    </th>
                    <th scope="col" class="px-6 py-3">
                        CodigoTurno
                    </th>
                    <th scope="col" class="px-6 py-3">
                        HoraEntrada
                    </th>
                    <th scope="col" class="px-6 py-3">
                        HoraRegistroEntrada
                    </th>
                    {{-- <th scope="col" class="px-6 py-3">
                        HoraMinimaEntrada
                    </th>
                    <th scope="col" class="px-6 py-3">
                        HoraMaximaEntrada
                    </th> --}}
                    <th scope="col" class="px-6 py-3">
                        HoraSalida
                    </th>
                    <th scope="col" class="px-6 py-3">
                        HoraRegistroSalida
                    {{-- </th>
                    <th scope="col" class="px-6 py-3">
                        HoraMinimaSalida
                    </th>
                    <th scope="col" class="px-6 py-3">
                        HoraMaximaSalida
                    </th> --}}
                    <th scope="col" class="px-6 py-3">
                        EstadoEntrada
                    </th>
                    <th scope="col" class="px-6 py-3">
                        EstadoSalida
                    </th>
                    {{-- <th scope="col" class="px-6 py-3">
                        Sanciones
                    </th> --}}
                    <th scope="col" class="px-6 py-3">
                        EstadoAsistencia
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Observaciones
                    </th>

                </tr>
            </thead>
            <tbody>
                @foreach ($asistencias as $asistencia)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            @if ($asistencia->persona)
                                {{ $asistencia->persona->Nombres }}
                                {{ $asistencia->persona->Paterno }}
                                {{ $asistencia->persona->Materno }}
                            @endif
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->CodigoTipoHorario}}
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->CodigoTurno}}
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->HoraEntrada}}
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->HoraRegistroEntrada}}
                        </th>
                        {{-- <th class="px-6 py-4">
                            {{$asistencia->HoraMinimaEntrada}}
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->HoraMaximaEntrada}}
                        </th> --}}
                        <th class="px-6 py-4">
                            {{$asistencia->HoraSalida}}
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->HoraRegistroSalida}}
                        </th>
                        {{-- <th class="px-6 py-4">
                            {{$asistencia->HoraMinimaSalida}}
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->HoraMaximaSalida}}
                        </th> --}}
                        <th class="px-6 py-4">
                            {{$asistencia->EstadoEntrada}}
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->EstadoSalida}}
                        </th>
                        {{-- <th class="px-6 py-4">
                            {{$asistencia->Sanciones}}
                        </th> --}}
                        <th class="px-6 py-4">
                            {{$asistencia->EstadoAsistencia}}
                        </th>
                        <th class="px-6 py-4">
                            {{$asistencia->Observaciones}}
                        </th>
                        
                        {{-- <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{route('admin.categories.edit', $category)}}" class="btn btn-blue text-xs">
                                    Editar
                                </a>

                                <form class="delete-form" action="{{ route('admin.categories.destroy', $category) }}" method="POST">
                                    
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-red text-xs ml-2" type="submit">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td> --}}
                    </tr>
                    
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $asistencias->links() }}


    <script>
        function actualizarHora() {
            const ahora = new Date();
            const hora = ahora.toLocaleTimeString('es-ES', { hour12: false });
            document.getElementById('hora-sistema').textContent = 'Hora del sistema: ' + hora;
        }
        setInterval(actualizarHora, 1000);
        actualizarHora();
    </script>
</x-layouts.public>