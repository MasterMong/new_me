<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />


        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>

        @if(app()->isLocal())
            <div class="mt-2 pt-4 border-t border-outline-variant/30">
                <p class="text-xs text-center text-on-surface/40 mb-3 uppercase tracking-wider font-medium">Dev Quick Login</p>
                <div class="flex flex-wrap justify-center gap-2">
                    @php
                        $quickUsers = [
                            ['email' => 'admin@me-learning.go.th',   'label' => 'Admin',    'color' => 'bg-red-100 text-red-700 hover:bg-red-200'],
                            ['email' => 'expert1@me-learning.go.th', 'label' => 'Expert 1', 'color' => 'bg-purple-100 text-purple-700 hover:bg-purple-200'],
                            ['email' => 'expert2@me-learning.go.th', 'label' => 'Expert 2', 'color' => 'bg-purple-100 text-purple-700 hover:bg-purple-200'],
                            ['email' => 'learner1@me-learning.go.th','label' => 'Learner 1','color' => 'bg-blue-100 text-blue-700 hover:bg-blue-200'],
                            ['email' => 'learner2@me-learning.go.th','label' => 'Learner 2','color' => 'bg-blue-100 text-blue-700 hover:bg-blue-200'],
                            ['email' => 'learner3@me-learning.go.th','label' => 'Learner 3','color' => 'bg-blue-100 text-blue-700 hover:bg-blue-200'],
                            ['email' => 'learner4@me-learning.go.th','label' => 'Learner 4','color' => 'bg-blue-100 text-blue-700 hover:bg-blue-200'],
                            ['email' => 'learner5@me-learning.go.th','label' => 'Learner 5','color' => 'bg-blue-100 text-blue-700 hover:bg-blue-200'],
                        ];
                    @endphp
                    @foreach ($quickUsers as $u)
                        <form method="POST" action="{{ route('quick-login') }}" class="inline">
                            @csrf
                            <input type="hidden" name="email" value="{{ $u['email'] }}" />
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-colors {{ $u['color'] }}">
                                {{ $u['label'] }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts::auth>
