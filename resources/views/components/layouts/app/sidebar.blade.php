<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse px-4 py-3" wire:navigate>
                <x-app-logo />
            </a>

            @auth
            @php $role = auth()->user()->role ?? ''; @endphp

            <flux:navlist variant="outline" class="px-2">

                {{-- ── TABLERO ──────────────────────────────────────────────────── --}}
                @if(in_array($role, ['admin', 'enfermeria', 'profesor']))
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Tablero</div>
                    <flux:navlist.group>
                        <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Tablero') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                <div class="border-t border-zinc-100 dark:border-zinc-800 my-2"></div>
                @endif

                {{-- ── PORTAL ALUMNO ──────────────────────────────────────────── --}}
                @if(in_array($role, ['alumno', 'super_alumno']))
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Mi Portal</div>
                    <flux:navlist.group>
                        <flux:navlist.item icon="user-circle" :href="route('portal.student')" :current="request()->routeIs('portal.student')" wire:navigate>
                            {{ __('Mi Portal') }}
                        </flux:navlist.item>
                        <flux:navlist.item icon="megaphone" :href="route('portal.announcements')" :current="request()->routeIs('portal.announcements')" wire:navigate>
                            {{ __('Comunicados') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                @endif

                {{-- ── ALUMNOS ────────────────────────────────────────────────── --}}
                @if(in_array($role, ['admin', 'enfermeria']))
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Alumnos</div>
                    <flux:navlist.group>
                        <flux:navlist.item icon="user-group" :href="route('students.index')" :current="request()->routeIs('students.*')" wire:navigate>
                            {{ __('Alumnos') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                <div class="border-t border-zinc-100 dark:border-zinc-800 my-2"></div>
                @endif

                {{-- ── PROFESORES ──────────────────────────────────────────────── --}}
                @if($role === 'admin')
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Profesores</div>
                    <flux:navlist.group>
                        <flux:navlist.item icon="academic-cap" :href="route('teachers.index')" :current="request()->routeIs('teachers.*')" wire:navigate>
                            {{ __('Profesores') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                <div class="border-t border-zinc-100 dark:border-zinc-800 my-2"></div>
                @endif

                {{-- ── ENFERMERÍA ───────────────────────────────────────────────── --}}
                @if(in_array($role, ['admin', 'enfermeria']))
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Enfermería</div>
                    <flux:navlist.group>
                        <flux:navlist.item icon="heart" :href="route('medical_checkups.report')" :current="request()->routeIs('medical_checkups.report')" wire:navigate>
                            {{ __('Revisiones Médicas') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                <div class="border-t border-zinc-100 dark:border-zinc-800 my-2"></div>
                @endif

                {{-- ── CLASES ───────────────────────────────────────────────────── --}}
                @if(in_array($role, ['admin', 'profesor']))
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Clases</div>
                    <flux:navlist.group>
                        @if($role === 'admin')
                        <flux:navlist.item icon="calendar-days" :href="route('subjects.index')" :current="request()->routeIs('subjects.*')" wire:navigate>
                            {{ __('Clases') }}
                        </flux:navlist.item>
                        @endif

                        <flux:navlist.item icon="clipboard-document-check" :href="route('attendance.index')" :current="request()->routeIs('attendance.*')" wire:navigate>
                            {{ __('Asistencia') }}
                        </flux:navlist.item>

                        @if($role === 'admin')
                        <flux:navlist.item icon="currency-dollar" :href="route('subject-prices.index')" :current="request()->routeIs('subject-prices.*')" wire:navigate>
                            {{ __('Valores de las clases') }}
                        </flux:navlist.item>
                        @endif

                        @if($role === 'profesor')
                        <flux:navlist.item icon="academic-cap" :href="route('portal.teacher')" :current="request()->routeIs('portal.teacher')" wire:navigate>
                            {{ __('Mi Portal') }}
                        </flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                </div>
                <div class="border-t border-zinc-100 dark:border-zinc-800 my-2"></div>
                @endif

                {{-- ── PAGOS ────────────────────────────────────────────────────── --}}
                @if($role === 'admin')
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Pagos</div>
                    <flux:navlist.group>
                         <flux:navlist.item icon="credit-card" :href="route('payment_methods.index')" :current="request()->routeIs('payment_methods.*')" wire:navigate>
                            {{ __('Tipos de pago') }}
                        </flux:navlist.item>
                        <flux:navlist.item icon="banknotes" :href="route('payments.index')" :current="(request()->routeIs('payments.*') && ! request()->routeIs('payments.history'))" wire:navigate>
                            {{ __('Pagos') }}
                        </flux:navlist.item>
                        <flux:navlist.item icon="clock" :href="route('payments.history')" :current="request()->routeIs('payments.history')" wire:navigate>
                            {{ __('Historial de pagos') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                <div class="border-t border-zinc-100 dark:border-zinc-800 my-2"></div>
                @endif

                {{-- ── REPORTES ─────────────────────────────────────────────────── --}}
                @if($role === 'admin')
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Reportes</div>
                    <flux:navlist.group>
                        <flux:navlist.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.index')" wire:navigate>
                            {{ __('Panel de reportes') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                @endif

                {{-- ── SISTEMA ──────────────────────────────────────────────────── --}}
                @if($role === 'admin')
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Sistema</div>
                    <flux:navlist.group>
                        <flux:navlist.item icon="megaphone" :href="route('announcements.index')" :current="request()->routeIs('announcements.*')" wire:navigate>
                            {{ __('Comunicados') }}
                        </flux:navlist.item>
                        <flux:navlist.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                            {{ __('Usuarios y Roles') }}
                        </flux:navlist.item>
                        <flux:navlist.item icon="arrow-down-tray" href="{{ route('backup.download') }}">
                            {{ __('Backup de Base de Datos') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                @endif

                @if(in_array($role, ['alumno', 'super_alumno', 'profesor', 'enfermeria']))
                <div class="mt-2 mb-3 px-1">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-2 mb-1">Cuenta</div>
                    <flux:navlist.group>
                        <flux:navlist.item icon="key" :href="route('password.edit')" :current="request()->routeIs('password.edit')" wire:navigate>
                            {{ __('Cambiar clave') }}
                        </flux:navlist.item>
                    </flux:navlist.group>
                </div>
                @endif

            </flux:navlist>
            @endauth

            <flux:spacer />

            <!-- Desktop User Menu -->
            @auth
                <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon:trailing="chevrons-up-down"
                        data-test="sidebar-menu-button"
                    />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>
                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->loginIdentifier() }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <script>window.location.href = "{{ route('login') }}";</script>
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>
                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->loginIdentifier() }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @else
                <script>window.location.href = "{{ route('login') }}";</script>
            @endauth
        </flux:header>

        {{ $slot }}

        @stack('scripts')
        @fluxScripts
    </body>
</html>