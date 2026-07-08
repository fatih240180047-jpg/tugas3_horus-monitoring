<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Supply Chain Intelligence Platform</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --warna-merah: #991b1b;
            --warna-merah-terang: #ef4444;
            --warna-charcoal: #0b0f19;
            --warna-charcoal-card: rgba(17, 24, 39, 0.65);
            --warna-charcoal-border: rgba(55, 65, 81, 0.5);
            --warna-teks-putih: #f9fafb;
            --warna-teks-abu: #9ca3af;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--warna-charcoal);
            background-image: 
                radial-gradient(at 10% 20%, rgba(153, 27, 27, 0.15) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(217, 119, 6, 0.08) 0px, transparent 50%);
            color: var(--warna-teks-putih);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background-color: var(--warna-charcoal-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--warna-charcoal-border);
            width: 100%;
            max-width: 440px;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-icon {
            font-size: 40px;
            color: var(--warna-merah-terang);
            margin-bottom: 16px;
            display: inline-block;
            filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.4));
        }

        .login-title {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .login-subtitle {
            color: var(--warna-teks-abu);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 13px;
            color: var(--warna-teks-abu);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-icon-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--warna-teks-abu);
            font-size: 16px;
        }

        .form-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background-color: rgba(11, 15, 25, 0.8);
            border: 1px solid var(--warna-charcoal-border);
            border-radius: 8px;
            color: var(--warna-teks-putih);
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--warna-merah-terang);
            box-shadow: 0 0 8px rgba(239, 68, 68, 0.2);
        }

        .flex-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            font-size: 13px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: var(--warna-teks-abu);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--warna-merah);
            color: var(--warna-teks-putih);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background-color: var(--warna-merah-terang);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        .error-message {
            color: #f87171;
            font-size: 12px;
            margin-top: 6px;
            display: block;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--warna-merah-terang);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            color: #f87171;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <span class="login-icon"><i class="fa-solid fa-shield-halved"></i></span>
            <h2 class="login-title">Horus Intelligence</h2>
            <p class="login-subtitle">Supply Chain Intelligence Platform</p>
        </div>

        @if($errors->any())
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('masuk.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email" class="form-label">Email Korporat</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input type="email" name="email" id="email" class="form-input" placeholder="nama@horus.local" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Kata Sandi</label>
                <div class="input-icon-wrapper">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input type="password" name="password" id="password" class="form-input" placeholder="••••••••" required>
                </div>
            </div>

            <div class="flex-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="ingat_saya" value="1">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <span>Autentikasi Aman</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>
    </div>

</body>
</html>
