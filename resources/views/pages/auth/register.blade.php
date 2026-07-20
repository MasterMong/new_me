<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <flux:select name="prefix" :label="__('Prefix')" required class="md:col-span-2">
                    <flux:select.option value="" disabled selected="{{ ! old('prefix') }}">{{ __('Select') }}</flux:select.option>
                    @foreach(App\Enums\UserPrefix::cases() as $prefix)
                        <flux:select.option value="{{ $prefix->value }}" :selected="old('prefix') === $prefix->value">{{ $prefix->value }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    name="first_name"
                    :label="__('First name')"
                    :value="old('first_name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="given-name"
                    class="md:col-span-2"
                />

                <flux:input
                    name="last_name"
                    :label="__('Last name')"
                    :value="old('last_name')"
                    type="text"
                    required
                    autocomplete="family-name"
                    class="md:col-span-2"
                />
            </div>

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <flux:input
                name="phone"
                :label="__('Phone number')"
                :value="old('phone')"
                type="tel"
                autocomplete="tel"
                placeholder="08x-xxx-xxxx"
            />

            <div x-data="{ positionId: '{{ old('position_id') }}' }" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select x-model="positionId" name="position_id" :label="__('Position')" required>
                    <flux:select.option value="" disabled selected="{{ ! old('position_id') }}">{{ __('Select a position') }}</flux:select.option>
                    @foreach($positions as $position)
                        <flux:select.option value="{{ $position->id }}" :selected="(string) old('position_id') === (string) $position->id">{{ $position->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    x-show="positionId === '{{ $otherPositionId }}'"
                    x-cloak
                    name="position_other"
                    :label="__('Specify other position')"
                    :value="old('position_other')"
                    placeholder="{{ __('Please specify...') }}"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:select name="affiliation_id" :label="__('Affiliation')" required>
                    <flux:select.option value="" disabled selected="{{ ! old('affiliation_id') }}">{{ __('Select an affiliation') }}</flux:select.option>
                    @foreach($affiliations as $affiliation)
                        <flux:select.option value="{{ $affiliation->id }}" :selected="(string) old('affiliation_id') === (string) $affiliation->id">{{ $affiliation->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    name="school_name"
                    :label="__('School / organization')"
                    :value="old('school_name')"
                    placeholder="{{ __('Enter your school or organization name') }}"
                />
            </div>

            <flux:select name="experience" :label="__('Educational work experience')" required>
                <flux:select.option value="" disabled selected="{{ ! old('experience') }}">{{ __('Select experience duration') }}</flux:select.option>
                @foreach(App\Enums\UserExperience::cases() as $exp)
                    <flux:select.option value="{{ $exp->value }}" :selected="old('experience') === $exp->value">{{ $exp->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
