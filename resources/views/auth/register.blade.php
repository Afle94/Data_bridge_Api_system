<x-layouts.auth>
    <div class="form-card">
        <div class="form-heading">
            <p class="eyebrow">New Operator</p>
            <h2>Create account</h2>
            <span>User code is reserved for VFP/source system mapping.</span>
        </div>

        <form method="POST" action="{{ route('register') }}" class="stacked-form">
            @csrf

            <label>
                <span>User Code</span>
                <input name="user_code" value="{{ old('user_code') }}" placeholder="USR-001" required autofocus>
                @error('user_code') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Name</span>
                <input name="name" value="{{ old('name') }}" placeholder="Operator name" required>
                @error('name') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="operator@example.com" required>
                @error('email') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Password</span>
                <input type="password" name="password" placeholder="Minimum 8 characters" required>
                @error('password') <small>{{ $message }}</small> @enderror
            </label>

            <label>
                <span>Confirm Password</span>
                <input type="password" name="password_confirmation" placeholder="Repeat password" required>
            </label>

            <button class="primary-button" type="submit">Register & Open Dashboard</button>
        </form>

        <p class="switch-link">Already registered? <a href="{{ route('login') }}">Login here</a></p>
    </div>
</x-layouts.auth>
