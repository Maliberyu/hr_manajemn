<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login - HR Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</head>

<body>

    <div class="container">
        <div class="card">

            <div class="title">HR Management</div>
            <div class="subtitle">Silakan login ke sistem</div>

            @if($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="input-group">
                    <input
                        type="email"
                        name="email"
                        class="input"
                        placeholder="Email"
                        value="{{ old('email') }}"
                        required>
                </div>

                <div class="input-group password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input"
                        placeholder="Password"
                        required>

                    <span class="toggle" onclick="togglePassword()">👁</span>
                </div>

                <button type="submit" class="btn">
                    Login
                </button>
            </form>

            <div class="forgot">
                <a href="/forgot-password">Lupa Password?</a>
            </div>

        </div>
    </div>

</body>

</html>