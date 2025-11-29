<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f6f6f6; padding:20px;">
  <div style="max-width:560px; margin:auto; background:#fff; padding:24px; border-radius:8px;">
    <h2 style="margin:0 0 8px 0; color:#111;">{{ $title }}</h2>
    @if(!empty($name))
      <p style="margin:0 0 12px 0">Halo {{ $name }},</p>
    @endif
    <p style="margin:0 0 8px 0">Gunakan kode berikut untuk verifikasi (berlaku 5 menit):</p>
    <div style="display:inline-block; padding:12px 20px; background:#f3f4f6; border-radius:6px; font-size:28px; letter-spacing:6px; font-weight:bold;">
      {{ $otp }}
    </div>
    <p style="margin-top:16px; color:#666;">Jika Anda tidak meminta kode ini, abaikan email ini.</p>
    <p style="margin-top:20px; color:#aaa; font-size:12px;">&copy; {{ date('Y') }} iHealth Edu</p>
  </div>
</body>
</html>
