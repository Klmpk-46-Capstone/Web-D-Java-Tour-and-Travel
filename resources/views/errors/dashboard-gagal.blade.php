<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gagal Memuat Dashboard - D'Java Tour &amp; Travel</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #eef2ff; color: #0f172a; font-family: 'DM Sans', Arial, sans-serif; }
        .box { max-width: 28rem; margin: 1rem; padding: 2rem; text-align: center; background: #fff; border: 1px solid #e2e8f0; border-radius: 1.25rem; box-shadow: 0 10px 30px rgba(15, 23, 42, .08); }
        h1 { margin: 0 0 .75rem; font-size: 1.25rem; }
        p { margin: 0 0 1.25rem; color: #475569; line-height: 1.6; }
        a { display: inline-block; padding: .75rem 1.5rem; border-radius: .75rem; background: #0f766e; color: #fff; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box" role="alert">
        <h1>D'Java Tour &amp; Travel</h1>
        <p>Gagal memuat data dashboard, silakan muat ulang halaman.</p>
        <a href="{{ url()->current() }}">Muat Ulang Halaman</a>
    </div>
</body>
</html>
