<x-layouts.app title="Ver profesor">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Datos del Profesor</h1>
            <div class="flex gap-2">
                <a href="{{ route('teachers.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded shadow transition">
                    <flux:icon name="arrow-left" class="h-4 w-4" />
                    Volver
                </a>
                <a href="{{ route('teachers.edit', $teacher) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded shadow transition">
                    <flux:icon name="pencil" class="h-4 w-4" />
                    Editar
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div id="flash-success" class="mb-4 p-3 rounded border border-green-200 bg-green-50 dark:bg-green-900/30 dark:border-green-800 text-green-800 dark:text-green-200 flex items-start gap-3 shadow-sm"
                 role="status" aria-live="polite" data-timeout="5000">
                <div class="flex-1 text-base leading-relaxed">
                    {{ session('success') }}
                </div>
                <button type="button"
                        class="ml-2 -mr-1 p-1 rounded hover:bg-green-100 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-[#29b1dc] text-green-800 dark:text-green-200"
                        aria-label="Cerrar mensaje" id="flash-success-close">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Detalles del profesor --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Información Personal</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-6 text-base">
                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">ID</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->id }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Nombre Completo</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->full_name }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Email</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->email ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Teléfono</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->phone ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Fecha de Nacimiento</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">
                        {{ $teacher->birth_date ? $teacher->birth_date->format('d/m/Y') : '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Edad</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">
                        @if($teacher->age)
                            {{ $teacher->age }} {{ $teacher->age === 1 ? 'año' : 'años' }}
                        @else
                            -
                        @endif
                    </dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-base font-medium text-gray-700 dark:text-gray-300">Dirección</dt>
                    <dd class="mt-2 text-gray-900 dark:text-gray-100 font-medium leading-relaxed">{{ $teacher->address ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Clases que dicta como Titular --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Clases como Titular
                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">({{ $teacher->subjects->count() }})</span>
            </h2>

            @if($teacher->subjects->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Día
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Horario
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Capacidad
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Alumnos
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($teacher->subjects as $subject)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->subjectType->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ ucfirst($subject->day) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->capacity }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                            {{ $subject->students->count() }} / {{ $subject->capacity }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <button type="button"
                                                onclick="confirmRemove({{ $subject->id }}, 'titular')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <flux:icon name="x-mark" class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                                {{-- Comentarios para esta clase --}}
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <td colspan="6" class="px-4 py-3">
                                        <div class="space-y-3">
                                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Comentarios</h4>
                                            
                                            {{-- Add comment form --}}
                                            <form onsubmit="event.preventDefault(); addComment({{ $subject->id }}, event.target.querySelector('textarea').value, event.target);" class="space-y-2">
                                                <textarea 
                                                    name="comment" 
                                                    rows="2" 
                                                    maxlength="1000"
                                                    placeholder="Agregar un comentario..."
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                                ></textarea>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-gray-500">Máx. 1000 caracteres</span>
                                                    <button type="submit" class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded">
                                                        Agregar
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            {{-- Comments list --}}
                                            <div class="space-y-2" id="comments-{{ $subject->id }}">
                                                @forelse($subject->comments as $comment)
                                                    <div class="p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded" id="comment-{{ $comment->id }}">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                                    <span class="font-semibold">{{ $comment->teacher->full_name }}</span>
                                                                    <span>•</span>
                                                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                                </div>
                                                                <p class="text-sm text-gray-700 dark:text-gray-300" id="comment-text-{{ $comment->id }}">
                                                                    {{ $comment->comment }}
                                                                </p>
                                                                <form id="edit-form-{{ $comment->id }}" style="display:none;" onsubmit="event.preventDefault(); updateComment({{ $comment->id }}, event.target.querySelector('textarea').value);" class="mt-2 space-y-2">
                                                                    <textarea 
                                                                        name="comment" 
                                                                        rows="2" 
                                                                        maxlength="1000"
                                                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-900"
                                                                    >{{ $comment->comment }}</textarea>
                                                                    <div class="flex gap-2">
                                                                        <button type="submit" class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded">Guardar</button>
                                                                        <button type="button" onclick="cancelEdit({{ $comment->id }})" class="px-3 py-1 text-sm bg-gray-500 hover:bg-gray-600 text-white rounded">Cancelar</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            @if($comment->canBeEditedBy($teacher))
                                                                <div class="flex gap-2 ml-3">
                                                                    <button onclick="showEditForm({{ $comment->id }})" class="text-orange-600 hover:text-orange-900 dark:text-orange-400">
                                                                        <flux:icon name="pencil" class="h-4 w-4" />
                                                                    </button>
                                                                    <button onclick="deleteComment({{ $comment->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                                        <flux:icon name="trash" class="h-4 w-4" />
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No hay comentarios aún.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                    Este profesor aún no tiene clases asignadas como titular.
                </p>
            @endif
        </div>

        {{-- Clases que dicta como Suplente --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Clases como Suplente
                <span class="text-sm font-normal text-gray-600 dark:text-gray-400">({{ $teacher->subjectsAsSubstitute->count() }})</span>
            </h2>

            @if($teacher->subjectsAsSubstitute->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Día
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Horario
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Capacidad
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Alumnos
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($teacher->subjectsAsSubstitute as $subject)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->subjectType->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ ucfirst($subject->day) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->capacity }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                            {{ $subject->students->count() }} / {{ $subject->capacity }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <button type="button"
                                                onclick="confirmRemove({{ $subject->id }}, 'substitute')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <flux:icon name="x-mark" class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                                {{-- Comentarios para esta clase --}}
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <td colspan="6" class="px-4 py-3">
                                        <div class="space-y-3">
                                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Comentarios</h4>
                                            
                                            {{-- Add comment form --}}
                                            <form onsubmit="event.preventDefault(); addComment({{ $subject->id }}, event.target.querySelector('textarea').value, event.target);" class="space-y-2">
                                                <textarea 
                                                    name="comment" 
                                                    rows="2" 
                                                    maxlength="1000"
                                                    placeholder="Agregar un comentario..."
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                                ></textarea>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-gray-500">Máx. 1000 caracteres</span>
                                                    <button type="submit" class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded">
                                                        Agregar
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            {{-- Comments list --}}
                                            <div class="space-y-2" id="comments-{{ $subject->id }}">
                                                @forelse($subject->comments as $comment)
                                                    <div class="p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded" id="comment-{{ $comment->id }}">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                                    <span class="font-semibold">{{ $comment->teacher->full_name }}</span>
                                                                    <span>•</span>
                                                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                                </div>
                                                                <p class="text-sm text-gray-700 dark:text-gray-300" id="comment-text-{{ $comment->id }}">
                                                                    {{ $comment->comment }}
                                                                </p>
                                                                <form id="edit-form-{{ $comment->id }}" style="display:none;" onsubmit="event.preventDefault(); updateComment({{ $comment->id }}, event.target.querySelector('textarea').value);" class="mt-2 space-y-2">
                                                                    <textarea 
                                                                        name="comment" 
                                                                        rows="2" 
                                                                        maxlength="1000"
                                                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-900"
                                                                    >{{ $comment->comment }}</textarea>
                                                                    <div class="flex gap-2">
                                                                        <button type="submit" class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded">Guardar</button>
                                                                        <button type="button" onclick="cancelEdit({{ $comment->id }})" class="px-3 py-1 text-sm bg-gray-500 hover:bg-gray-600 text-white rounded">Cancelar</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            @if($comment->canBeEditedBy($teacher))
                                                                <div class="flex gap-2 ml-3">
                                                                    <button onclick="showEditForm({{ $comment->id }})" class="text-orange-600 hover:text-orange-900 dark:text-orange-400">
                                                                        <flux:icon name="pencil" class="h-4 w-4" />
                                                                    </button>
                                                                    <button onclick="deleteComment({{ $comment->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                                        <flux:icon name="trash" class="h-4 w-4" />
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No hay comentarios aún.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                {{-- Comentarios para esta clase (suplente) --}}
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <td colspan="6" class="px-4 py-3">
                                        <div class="space-y-3">
                                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Comentarios</h4>
                                            
                                            {{-- Add comment form --}}
                                            <form onsubmit="event.preventDefault(); addComment({{ $subject->id }}, event.target.querySelector('textarea').value, event.target);" class="space-y-2">
                                                <textarea 
                                                    name="comment" 
                                                    rows="2" 
                                                    maxlength="1000"
                                                    placeholder="Agregar un comentario..."
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                                ></textarea>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-gray-500">Máx. 1000 caracteres</span>
                                                    <button type="submit" class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded">
                                                        Agregar
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            {{-- Comments list --}}
                                            <div class="space-y-2" id="comments-{{ $subject->id }}">
                                                @forelse($subject->comments as $comment)
                                                    <div class="p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded" id="comment-{{ $comment->id }}">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                                    <span class="font-semibold">{{ $comment->teacher->full_name }}</span>
                                                                    <span>•</span>
                                                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                                </div>
                                                                <p class="text-sm text-gray-700 dark:text-gray-300" id="comment-text-{{ $comment->id }}">
                                                                    {{ $comment->comment }}
                                                                </p>
                                                                <form id="edit-form-{{ $comment->id }}" style="display:none;" onsubmit="event.preventDefault(); updateComment({{ $comment->id }}, event.target.querySelector('textarea').value);" class="mt-2 space-y-2">
                                                                    <textarea 
                                                                        name="comment" 
                                                                        rows="2" 
                                                                        maxlength="1000"
                                                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-900"
                                                                    >{{ $comment->comment }}</textarea>
                                                                    <div class="flex gap-2">
                                                                        <button type="submit" class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded">Guardar</button>
                                                                        <button type="button" onclick="cancelEdit({{ $comment->id }})" class="px-3 py-1 text-sm bg-gray-500 hover:bg-gray-600 text-white rounded">Cancelar</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            @if($comment->canBeEditedBy($teacher))
                                                                <div class="flex gap-2 ml-3">
                                                                    <button onclick="showEditForm({{ $comment->id }})" class="text-orange-600 hover:text-orange-900 dark:text-orange-400">
                                                                        <flux:icon name="pencil" class="h-4 w-4" />
                                                                    </button>
                                                                    <button onclick="deleteComment({{ $comment->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                                        <flux:icon name="trash" class="h-4 w-4" />
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No hay comentarios aún.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                    Este profesor aún no tiene clases asignadas como suplente.
                </p>
            @endif
        </div>

        {{-- Asignar a nuevas clases --}}
        <div class="bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-800 rounded-lg p-6 shadow-sm mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Asignar a Clases
            </h2>

            @if($availableSubjects->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Día
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Horario
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Profesor Actual
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Suplente Actual
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                            @foreach($availableSubjects as $subject)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->subjectType->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ ucfirst($subject->day) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->teacher ? $subject->teacher->full_name : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $subject->substituteTeacher ? $subject->substituteTeacher->full_name : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-2">
                                            @if(!$subject->teacher_id)
                                                <button type="button"
                                                        onclick="assignTeacher({{ $subject->id }}, 'titular')"
                                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded transition"
                                                        title="Asignar como titular">
                                                    <flux:icon name="user" class="h-3 w-3" />
                                                    Titular
                                                </button>
                                            @endif
                                            @if(!$subject->substitute_teacher_id)
                                                <button type="button"
                                                        onclick="assignTeacher({{ $subject->id }}, 'substitute')"
                                                        class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-green-500 hover:bg-green-600 text-white rounded transition"
                                                        title="Asignar como suplente">
                                                    <flux:icon name="user-plus" class="h-3 w-3" />
                                                    Suplente
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                {{-- Comentarios para esta clase --}}
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    <td colspan="6" class="px-4 py-3">
                                        <div class="space-y-3">
                                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Comentarios</h4>
                                            
                                            {{-- Add comment form --}}
                                            <form onsubmit="event.preventDefault(); addComment({{ $subject->id }}, event.target.querySelector('textarea').value, event.target);" class="space-y-2">
                                                <textarea 
                                                    name="comment" 
                                                    rows="2" 
                                                    maxlength="1000"
                                                    placeholder="Agregar un comentario..."
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                                                ></textarea>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-gray-500">Máx. 1000 caracteres</span>
                                                    <button type="submit" class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded">
                                                        Agregar
                                                    </button>
                                                </div>
                                            </form>
                                            
                                            {{-- Comments list --}}
                                            <div class="space-y-2" id="comments-{{ $subject->id }}">
                                                @forelse($subject->comments as $comment)
                                                    <div class="p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded" id="comment-{{ $comment->id }}">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                                    <span class="font-semibold">{{ $comment->teacher->full_name }}</span>
                                                                    <span>•</span>
                                                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                                </div>
                                                                <p class="text-sm text-gray-700 dark:text-gray-300" id="comment-text-{{ $comment->id }}">
                                                                    {{ $comment->comment }}
                                                                </p>
                                                                <form id="edit-form-{{ $comment->id }}" style="display:none;" onsubmit="event.preventDefault(); updateComment({{ $comment->id }}, event.target.querySelector('textarea').value);" class="mt-2 space-y-2">
                                                                    <textarea 
                                                                        name="comment" 
                                                                        rows="2" 
                                                                        maxlength="1000"
                                                                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-900"
                                                                    >{{ $comment->comment }}</textarea>
                                                                    <div class="flex gap-2">
                                                                        <button type="submit" class="px-3 py-1 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded">Guardar</button>
                                                                        <button type="button" onclick="cancelEdit({{ $comment->id }})" class="px-3 py-1 text-sm bg-gray-500 hover:bg-gray-600 text-white rounded">Cancelar</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            @if($comment->canBeEditedBy($teacher))
                                                                <div class="flex gap-2 ml-3">
                                                                    <button onclick="showEditForm({{ $comment->id }})" class="text-orange-600 hover:text-orange-900 dark:text-orange-400">
                                                                        <flux:icon name="pencil" class="h-4 w-4" />
                                                                    </button>
                                                                    <button onclick="deleteComment({{ $comment->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                                        <flux:icon name="trash" class="h-4 w-4" />
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">No hay comentarios aún.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                    No hay clases disponibles para asignar a este profesor.
                </p>
            @endif
        </div>

        {{-- Botón de eliminar --}}
        <div class="mt-6 flex justify-end">
            <button type="button"
                    onclick="confirmDelete()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded shadow transition">
                <flux:icon name="trash" class="h-4 w-4" />
                Eliminar Profesor
            </button>
        </div>
    </div>

    <script>
        // Auto-close flash messages
        setTimeout(() => {
            const flash = document.getElementById('flash-success');
            if (flash) flash.style.display = 'none';
        }, 5000);

        document.getElementById('flash-success-close')?.addEventListener('click', () => {
            document.getElementById('flash-success').style.display = 'none';
        });

        // Asignar profesor a clase
        function assignTeacher(subjectId, role) {
            const roleText = role === 'titular' ? 'titular' : 'suplente';
            Swal.fire({
                title: '¿Asignar como ' + roleText + '?',
                text: 'Se asignará a {{ $teacher->full_name }} como ' + roleText + ' de esta clase.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, asignar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('teachers.assignToSubject', $teacher) }}';
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    
                    const subjectInput = document.createElement('input');
                    subjectInput.type = 'hidden';
                    subjectInput.name = 'subject_id';
                    subjectInput.value = subjectId;
                    
                    const roleInput = document.createElement('input');
                    roleInput.type = 'hidden';
                    roleInput.name = 'role';
                    roleInput.value = role;
                    
                    form.appendChild(csrfInput);
                    form.appendChild(subjectInput);
                    form.appendChild(roleInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Remover profesor de clase
        function confirmRemove(subjectId, role) {
            const roleText = role === 'titular' ? 'titular' : 'suplente';
            Swal.fire({
                title: '¿Remover como ' + roleText + '?',
                text: 'Se removerá a {{ $teacher->full_name }} como ' + roleText + ' de esta clase.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, remover',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('teachers.removeFromSubject', $teacher) }}';
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    
                    const subjectInput = document.createElement('input');
                    subjectInput.type = 'hidden';
                    subjectInput.name = 'subject_id';
                    subjectInput.value = subjectId;
                    
                    const roleInput = document.createElement('input');
                    roleInput.type = 'hidden';
                    roleInput.name = 'role';
                    roleInput.value = role;
                    
                    form.appendChild(csrfInput);
                    form.appendChild(subjectInput);
                    form.appendChild(roleInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Delete confirmation
        function confirmDelete() {
            Swal.fire({
                title: '¿Estás seguro?',
                html: `Vas a eliminar al profesor <strong>{{ $teacher->full_name }}</strong>.<br><br>
                       @if($teacher->subjects->count() > 0 || $teacher->subjectsAsSubstitute->count() > 0)
                       Las clases asignadas quedarán sin este profesor.
                       @endif`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('teachers.destroy', $teacher) }}';
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    
                    form.appendChild(csrfInput);
                    form.appendChild(methodInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Comments functionality
        function addComment(subjectId, commentText, form) {
            if (!commentText.trim()) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`/subjects/${subjectId}/comments`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    comment: commentText,
                    teacher_id: {{ $teacher->id }}
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Comentario agregado',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Reload page to show new comment
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al agregar el comentario.'
                });
            });
        }

        function showEditForm(commentId) {
            document.getElementById(`comment-text-${commentId}`).style.display = 'none';
            document.getElementById(`edit-form-${commentId}`).style.display = 'block';
        }

        function cancelEdit(commentId) {
            document.getElementById(`comment-text-${commentId}`).style.display = 'block';
            document.getElementById(`edit-form-${commentId}`).style.display = 'none';
        }

        function updateComment(commentId, commentText) {
            if (!commentText.trim()) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch(`/comments/${commentId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    comment: commentText,
                    teacher_id: {{ $teacher->id }}
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Comentario actualizado',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // Reload page to show updated comment
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al actualizar el comentario.'
                });
            });
        }

        function deleteComment(commentId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Se eliminará este comentario permanentemente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    fetch(`/comments/${commentId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            teacher_id: {{ $teacher->id }}
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Comentario eliminado',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            // Reload page to reflect deletion
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al eliminar el comentario.'
                        });
                    });
                }
            });
        }
    </script>
</x-layouts.app>
