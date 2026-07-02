<x-layouts.auth>
    <div class="form-card">
        <div class="form-heading">
            <p class="eyebrow">Secure Login</p>
            <h2>Welcome back</h2>
            <span>Log in to access the data sync dashboard.</span>
        </div>

        <form method="POST" action="{{ route('login') }}" class="stacked-form">
            @csrf

            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="operator@example.com" required autofocus>
                @error('email') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Password</span>
                <input type="password" name="password" placeholder="Your password" required>
                @error('password') <small>{{ $message }}</small> @enderror
            </label>

            <label class="check-row">
                <input type="checkbox" name="remember" value="1">
                <span>Remember this device</span>
            </label>

            <button class="primary-button" type="submit">Login</button>
        </form>

        <p class="switch-link">New user? <a href="{{ route('register') }}">Create account</a></p>
    </div>
</x-layouts.auth>
