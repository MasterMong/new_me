<?php

use App\Concerns\ProfileValidationRules;
/* @chisel-email-verification */
use Illuminate\Contracts\Auth\MustVerifyEmail;
/* @end-chisel-email-verification */
use Flux\Flux;
use App\Models\Position;
use App\Models\Affiliation;
use App\Enums\UserExperience;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $prefix = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public ?int $position_id = null;
    public string $position_other = '';
    public ?int $affiliation_id = null;
    public string $school_name = '';
    public string $experience = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->prefix = $user->prefix?->value ?? '';
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->position_id = $user->position_id;
        $this->position_other = $user->position_other ?? '';
        $this->affiliation_id = $user->affiliation_id;
        $this->school_name = $user->school_name ?? '';
        $this->experience = $user->experience?->value ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    #[Computed]
    public function positions()
    {
        return Position::orderBy('sort_order')->get();
    }

    #[Computed]
    public function affiliations()
    {
        return Affiliation::all();
    }

    #[Computed]
    public function isOtherPosition(): bool
    {
        $otherPosition = Position::where('name', 'อื่น ๆ')->first();
        return $this->position_id == ($otherPosition?->id ?? -1);
    }

    /* @chisel-email-verification */
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

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
    /* @end-chisel-email-verification */
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your personal and professional information')">
        <div class="mb-8 flex items-center gap-4 p-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-xl border border-zinc-200 dark:border-zinc-800">
            <div class="flex size-16 items-center justify-center rounded-full bg-primary-600 text-white text-xl font-bold shadow-sm">
                {{ Auth::user()->initials() }}
            </div>
            <div>
                <flux:heading size="lg">{{ Auth::user()->fullName() }}</flux:heading>
                <flux:subheading>{{ Auth::user()->email }}</flux:subheading>
            </div>
        </div>

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-10">
            {{-- Basic Info --}}
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <flux:icon.user variant="mini" class="text-zinc-400" />
                    <flux:heading size="lg" separator>{{ __('Basic Information') }}</flux:heading>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                    <flux:select wire:model="prefix" :label="__('Prefix')" required class="md:col-span-1">
                        @foreach(App\Enums\UserPrefix::cases() as $prefix)
                            <flux:select.option :value="$prefix->value">{{ $prefix->value }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="first_name" :label="__('First Name')" type="text" required class="md:col-span-2" autocomplete="given-name" />
                    <flux:input wire:model="last_name" :label="__('Last Name')" type="text" required class="md:col-span-3" autocomplete="family-name" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model="email" :label="__('Email Address')" type="email" required autocomplete="email" icon="envelope" />
                    <flux:input wire:model="phone" :label="__('Phone Number')" type="tel" icon="phone" placeholder="08x-xxx-xxxx" />
                </div>
            </div>

            {{-- Professional Info --}}
            <div class="space-y-6">
                <div class="flex items-center gap-2">
                    <flux:icon.briefcase variant="mini" class="text-zinc-400" />
                    <flux:heading size="lg" separator>{{ __('Professional Information') }}</flux:heading>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:select wire:model.live="position_id" :label="__('Current Position')" required>
                        <flux:select.option disabled>{{ __('Select a position') }}</flux:select.option>
                        @foreach($this->positions as $position)
                            <flux:select.option :value="$position->id">{{ $position->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    @if($this->isOtherPosition)
                        <flux:input 
                            wire:model="position_other" 
                            :label="__('Specify Other Position')" 
                            placeholder="{{ __('Please specify...') }}"
                            required 
                        />
                    @else
                        <div class="hidden md:block"></div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:select wire:model="affiliation_id" :label="__('Affiliation')" required>
                        <flux:select.option disabled>{{ __('Select an affiliation') }}</flux:select.option>
                        @foreach($this->affiliations as $affiliation)
                            <flux:select.option :value="$affiliation->id">{{ $affiliation->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="school_name" :label="__('School/Organization')" placeholder="{{ __('Enter your school or organization name') }}" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:select wire:model="experience" :label="__('Educational Work Experience')" required>
                        <flux:select.option disabled>{{ __('Select experience duration') }}</flux:select.option>
                        @foreach(App\Enums\UserExperience::cases() as $exp)
                            <flux:select.option :value="$exp->value">
                                @if($exp->value === '<2y') น้อยกว่า 2 ปี
                                @elseif($exp->value === '2-5y') 2-5 ปี
                                @elseif($exp->value === '5-10y') 5-10 ปี
                                @elseif($exp->value === '10-20y') 10-20 ปี
                                @else มากกว่า 20 ปี
                                @endif
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <flux:button variant="primary" type="submit" icon="check">
                    {{ __('Update Profile') }}
                </flux:button>
            </div>
        </form>

        {{-- Email Verification Notice --}}
        @if ($this->hasUnverifiedEmail)
            <div class="mt-8 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl">
                <div class="flex gap-3">
                    <flux:icon.exclamation-triangle class="text-yellow-600 dark:text-yellow-400" />
                    <div>
                        <flux:text class="font-medium text-yellow-800 dark:text-yellow-200">
                            {{ __('Your email address is unverified.') }}
                        </flux:text>
                        <flux:text class="mt-1 text-yellow-700 dark:text-yellow-300">
                            <flux:link class="cursor-pointer font-semibold underline decoration-yellow-400 hover:decoration-yellow-600" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>
                        
                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($this->showDeleteUser)
            <div class="mt-12">
                <livewire:pages::settings.delete-user-form />
            </div>
        @endif
    </x-pages::settings.layout>
</section>
