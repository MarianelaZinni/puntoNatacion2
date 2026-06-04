<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Livewire\Volt\Component;

$announcements = $announcements ?? collect();

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $dni = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (! $user) {
            redirect()->route('login')->send();
            return;
        }
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email ?? '';
        $this->dni = Auth::user()->dni ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validator = Validator::make([
            'name' => $this->name,
            'email' => $this->email,
            'dni' => $this->dni,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'dni' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique(User::class, 'dni')->ignore($user->id),
            ],
        ]);

        $validator->after(function ($validator) use ($user) {
            if ($user->role === User::ROLE_ALUMNO) {
                if (blank($this->email) && blank($this->dni)) {
                    $message = 'Completá al menos email o DNI.';
                    $validator->errors()->add('email', $message);
                    $validator->errors()->add('dni', $message);
                }

                return;
            }

            if (blank($this->email)) {
                $validator->errors()->add('email', 'El campo email es obligatorio.');
            }
        });

        $validated = $validator->validate();
        $validated['email'] = filled($validated['email'] ?? null) ? $validated['email'] : null;
        $validated['dni'] = filled($validated['dni'] ?? null) ? $validated['dni'] : null;

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and access data')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" :required="auth()->user()->role !== \App\Models\User::ROLE_ALUMNO" autocomplete="email" />
            </div>

            <div>
                <flux:input wire:model="dni" :label="__('DNI')" type="text" autocomplete="off" />

                @if (auth()->user()->role === \App\Models\User::ROLE_ALUMNO)
                    <flux:text class="mt-2">
                        {{ __('For student users, complete at least email or DNI.') }}
                    </flux:text>
                @endif

            </div>

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
                    <flux:text>
                        {{ __('Your email address is unverified.') }}

                        <flux:link class="cursor-pointer text-sm" wire:click.prevent="resendVerificationNotification">
                            {{ __('Click here to re-send the verification email.') }}
                        </flux:link>
                    </flux:text>

                    @if (session('status') === 'verification-link-sent')
                        <flux:text class="mt-2 font-medium !text-green-600 !dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </flux:text>
                    @endif
                </div>
            @endif

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="w-full sm:w-auto">
                    <flux:button variant="primary" type="submit" class="w-full sm:w-auto" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>