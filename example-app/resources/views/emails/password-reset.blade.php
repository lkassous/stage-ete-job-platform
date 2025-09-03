<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <h2>Réinitialisation de mot de passe</h2>
    </div>
    
    <div class="content">
        <p>Bonjour,</p>
        
        <p>Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.</p>
        
        <p style="text-align: center;">
            <a href="{{ $url }}" class="button">Réinitialiser le mot de passe</a>
        </p>
        
        <p>Ce lien de réinitialisation expirera dans {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.</p>
        
        <p>Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action n'est requise.</p>
        
        <p>Si vous avez des difficultés à cliquer sur le bouton "Réinitialiser le mot de passe", copiez et collez l'URL ci-dessous dans votre navigateur web :</p>
        
        <p style="word-break: break-all; color: #666; font-size: 14px;">{{ $url }}</p>
    </div>
    
    <div class="footer">
        <p>Cordialement,<br>L'équipe {{ config('app.name') }}</p>
        <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>
</body>
</html>
