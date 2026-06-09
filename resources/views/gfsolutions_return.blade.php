<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiement - Merci E</title>
    <style>
        body { font-family: -apple-system, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f5f5f5; }
        .card { background: white; border-radius: 16px; padding: 40px; text-align: center; max-width: 360px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
        .icon { font-size: 64px; margin-bottom: 16px; }
        .title { font-size: 22px; font-weight: 600; margin-bottom: 8px; }
        .subtitle { font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class="card">
        @if($status === 'completed')
            <div class="icon">✅</div>
            <div class="title">Paiement réussi</div>
            <div class="subtitle">Votre portefeuille a été rechargé.</div>
        @elseif($status === 'failed')
            <div class="icon">❌</div>
            <div class="title">Paiement échoué</div>
            <div class="subtitle">Le paiement n'a pas abouti. Veuillez réessayer.</div>
        @else
            <div class="icon">⏳</div>
            <div class="title">Traitement en cours</div>
            <div class="subtitle">Votre paiement est en cours de vérification.</div>
        @endif
    </div>
    <script>
        // Signal to the WebView that payment is done
        // The app detects this URL contains 'gfsolutions/return' and closes the WebView
        window.GFS_PAYMENT_STATUS = '{{ $status }}';
        window.GFS_ORDER_ID = '{{ $orderId }}';
    </script>
</body>
</html>
