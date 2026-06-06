<x-layouts.app title="Mi Portal">
    <div class="max-w-4xl mx-auto py-8 px-4">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Mi Portal</h1>
            <a href="{{ route('password.edit') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded text-white bg-[#29b1dc] hover:bg-[#24a8cf] transition">
                <flux:icon name="key" class="h-4 w-4" />
                Cambiar clave
            </a>
        </div>

        {{-- Comunicados --}}
        @if(!empty($announcements) && $announcements->isNotEmpty())
        <div class="mb-8 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <flux:icon name="megaphone" class="h-5 w-5 text-[#29b1dc]" />
                Comunicados
            </h2>
            @foreach(($announcements ?? collect()) as $announcement)
            <div class="bg-[#eaf7fc] dark:bg-[#29b1dc]/10 border border-[#29b1dc]/30 dark:border-[#29b1dc]/40 rounded-lg p-4 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ $announcement->title }}</h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $announcement->body }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ $announcement->created_at->diffForHumans() }}</p>
                    </div>
                    <form action="{{ route('portal.announcements.read', $announcement) }}" method="POST" class="flex-shrink-0 mt-1">
                        @csrf
                        <button type="submit"
                                title="Marcar como leído"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-white dark:bg-zinc-800 border border-[#29b1dc]/50 text-[#29b1dc] hover:bg-[#29b1dc] hover:text-white dark:hover:bg-[#29b1dc] dark:hover:text-white transition">
                            <flux:icon name="check" class="h-3.5 w-3.5" />
                            Leído
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Notas de profesores --}}
        @if(!empty($notes) && $notes->isNotEmpty())
        <div class="mb-8 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" class="h-5 w-5 text-[#29b1dc]" />
                    Notas de profesores
                </h2>
                <a href="{{ route('portal.notes') }}" wire:navigate
                   class="text-sm text-[#29b1dc] hover:underline">Ver todas</a>
            </div>
            @foreach($notes as $note)
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $note->title ?: 'Nota de clase' }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Alumno: {{ $note->student?->name ?? '—' }} ·
                            Clase: {{ $note->subject?->subjectType?->description ?? '—' }} ·
                            {{ $note->created_at->diffForHumans() }}
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line mt-2">{{ $note->body }}</p>
                    </div>
                    <form action="{{ route('portal.notes.read', $note) }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-full border border-[#29b1dc]/50 px-3 py-1.5 text-xs font-medium text-[#29b1dc] transition hover:bg-[#29b1dc] hover:text-white">
                            <flux:icon name="check" class="h-3.5 w-3.5" />
                            Leída
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($students->isEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-6 text-center text-amber-800 dark:text-amber-300">
                <p class="font-medium">No tienes alumnos vinculados a tu cuenta.</p>
                <p class="text-sm mt-1">Contactá al administrador para vincular tu perfil.</p>
            </div>
        @else
            @foreach($students as $student)
            <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg shadow-sm mb-8">
                {{-- Student header --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-800 bg-gray-50 dark:bg-zinc-800/50 rounded-t-lg">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $student->name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">DNI: {{ $student->dni }}</p>
                        </div>
                        @php
                            $debtAmt = (float) ($student->debt ?? 0);
                            $ispaused = $student->isCurrentlyPaused();
                        @endphp
                        @if($ispaused)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                <flux:icon name="pause-circle" class="h-4 w-4" />
                                Pausado
                            </span>
                        @elseif($debtAmt > 0)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                Deuda: ${{ number_format($debtAmt, 2, ',', '.') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                Al día
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Classes --}}
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
                            Clases Inscriptas
                        </h3>
                        @if($student->subjects->isEmpty())
                            <p class="text-sm text-gray-400">Sin clases inscriptas.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach($student->subjects as $subject)
                                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <flux:icon name="calendar-days" class="h-4 w-4 mt-0.5 text-[#29b1dc] flex-shrink-0" />
                                    <span>
                                        {{ $subject->subjectType?->description ?? '—' }}
                                        · {{ $subject->day }}
                                        {{ substr($subject->start_time, 0, 5) }}–{{ substr($subject->end_time, 0, 5) }}
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Pending periods --}}
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
                            Períodos Adeudados
                        </h3>
                        @php $unpaid = $student->unpaid_periods ?? []; @endphp
                        @if(empty($unpaid))
                            <p class="text-sm text-green-600 dark:text-green-400 font-medium">✓ Sin deuda pendiente.</p>
                        @else
                            <ul class="space-y-1">
                                @foreach($unpaid as $p)
                                <li class="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300">
                                    <span>{{ \Carbon\Carbon::createFromFormat('Y-m', $p['period'])->translatedFormat('F Y') }}</span>
                                    <span class="text-red-600 dark:text-red-400 font-medium">${{ number_format($p['deficit'], 2, ',', '.') }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- Payment history --}}
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-3">
                            Últimos Pagos
                        </h3>
                        @if($student->payments->isEmpty())
                            <p class="text-sm text-gray-400">Sin pagos registrados.</p>
                        @else
                            <div class="overflow-x-auto -mx-2 sm:mx-0">
                                <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-zinc-700">
                                    <thead>
                                        <tr class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase">
                                            <th class="pb-2 pr-4 whitespace-nowrap">Fecha</th>
                                            <th class="pb-2 pr-4 whitespace-nowrap">Período</th>
                                            <th class="pb-2 pr-4 whitespace-nowrap">Monto</th>
                                            <th class="pb-2 whitespace-nowrap">Método</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                        @foreach($student->payments->take(10) as $payment)
                                        <tr>
                                            <td class="py-2 pr-4 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                                {{ $payment->payment_date?->format('d/m/Y') ?? '—' }}
                                            </td>
                                            <td class="py-2 pr-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                                {{ $payment->payment_period ? \Carbon\Carbon::parse($payment->payment_period)->translatedFormat('M Y') : '—' }}
                                            </td>
                                            <td class="py-2 pr-4 font-medium text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                ${{ number_format($payment->amount, 2, ',', '.') }}
                                            </td>
                                            <td class="py-2 text-gray-500 dark:text-gray-400">
                                                {{ $payment->paymentMethod?->name ?? '—' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
            @endforeach
        @endif
    </div>
</x-layouts.app>