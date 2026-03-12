<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Montserrat', Arial, sans-serif; background: #000; color: #e4e4e7; margin: 0; padding: 0; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 32px 24px; }
        .header { text-align: center; padding-bottom: 24px; border-bottom: 1px solid #27272a; margin-bottom: 24px; }
        .header h1 { font-family: 'Bebas Neue', Arial, sans-serif; color: #E7FF57; font-size: 28px; margin: 0; letter-spacing: 2px; text-transform: uppercase; }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #71717a; margin-bottom: 4px; }
        .value { font-size: 15px; color: #e4e4e7; margin-bottom: 20px; }
        .message-box { background: #18181b; border: 1px solid #27272a; border-radius: 4px; padding: 16px; margin: 20px 0; }
        .message-box p { margin: 0; font-size: 15px; line-height: 1.6; color: #fff; }
        .cta { display: inline-block; background: #E7FF57; color: #000; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 1.5px; padding: 12px 24px; text-decoration: none; margin-top: 24px; }
        .footer { margin-top: 32px; padding-top: 20px; border-top: 1px solid #27272a; text-align: center; }
        .footer p { font-size: 12px; color: #52525b; margin: 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Nieuw chatbericht</h1>
        </div>

        <div class="label">Character</div>
        <div class="value">{{ $character->full_name }}</div>

        <div class="label">Bericht van bezoeker</div>
        <div class="message-box">
            <p>{{ $visitorMessage }}</p>
        </div>

        <div class="label">Bezoeker IP</div>
        <div class="value">{{ $visitorIp }}</div>

        <div class="label">Tijdstip</div>
        <div class="value">{{ now()->format('d-m-Y H:i') }}</div>

        <a href="{{ route('admin.chats') }}" class="cta">Bekijk in CMS</a>

        <div class="footer">
            <p>Je ontvangt deze e-mail omdat er geen admin online was toen dit bericht binnenkwam.</p>
        </div>
    </div>
</body>
</html>
