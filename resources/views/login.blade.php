<!DOCTYPE html>
<html>
<head>
    <title>Login - {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:sans-serif;background:#f5f5f5;margin:0;">
    <div style="background:white;padding:40px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);max-width:400px;width:100%;">
        <h2 style="margin:0 0 10px;text-align:center;">MCP OAuth Login</h2>
        <p style="color:#666;text-align:center;margin:0 0 30px;">Login to authorize the application</p>
        
        @if($errors->any())
            <div style="background:#fee;color:#c33;padding:10px;border-radius:4px;margin-bottom:20px;">
                {{ $errors->first() }}
            </div>
        @endif
        
        <form method="POST" action="/login">
            @csrf
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:500;">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:500;">Password</label>
                <input type="password" name="password" required
                    style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px;">
            </div>
            
            <button type="submit" style="width:100%;padding:12px;font-size:16px;cursor:pointer;background:#4f46e5;color:white;border:none;border-radius:6px;font-weight:500;">
                Login
            </button>
        </form>
    </div>
</body>
</html>
