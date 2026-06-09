<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FEEDTAN DIGITAL PAYMENT SYSTEM - Recovery Codes</title>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: #f9fafb;
            color: #1f2937;
            margin: 0;
            padding: 40px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #10b981;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #059669;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }
        .info-box {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .info-box h2 {
            color: #065f46;
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .info-box ul {
            margin: 0;
            padding-left: 20px;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.8;
        }
        .recovery-codes {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 40px;
        }
        .code-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            color: #065f46;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 20px;
        }
        .user-info {
            margin-bottom: 30px;
            font-size: 14px;
            color: #374151;
        }
        .user-info .label {
            font-weight: bold;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FEEDTAN DIGITAL PAYMENT SYSTEM</h1>
            <p>Two-Factor Authentication Recovery Codes</p>
        </div>
        
        <div class="user-info">
            <p><span class="label">User:</span> {{ $user->name }}</p>
            <p><span class="label">Email:</span> {{ $user->email }}</p>
            <p><span class="label">Generated:</span> {{ now()->format('d M, Y H:i:s') }}</p>
        </div>

        <div class="info-box">
            <h2>Important Instructions</h2>
            <ul>
                <li>These recovery codes can be used to access your account if you lose your authenticator device.</li>
                <li>Each code can be used only once.</li>
                <li>Store these codes in a secure location and do not share them with anyone.</li>
                <li>You can generate new recovery codes from your profile at any time.</li>
            </ul>
        </div>

        <h3 style="margin-bottom: 20px; color: #059669;">Your Recovery Codes</h3>
        <div class="recovery-codes">
            @foreach($recoveryCodes as $code)
                <div class="code-item">{{ $code }}</div>
            @endforeach
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} FEEDTAN DIGITAL PAYMENT SYSTEM. All rights reserved.</p>
            <p>Powered by FeedTan Team</p>
        </div>
    </div>
</body>
</html>