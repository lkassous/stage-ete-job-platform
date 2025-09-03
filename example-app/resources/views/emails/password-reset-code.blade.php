<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        .title {
            color: #2c3e50;
            font-size: 28px;
            margin: 0;
            font-weight: 600;
        }
        .greeting {
            font-size: 18px;
            color: #34495e;
            margin-bottom: 20px;
        }
        .code-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
        }
        .code-label {
            font-size: 16px;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        .reset-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 8px;
            display: inline-block;
            margin: 10px 0;
        }
        .code-info {
            font-size: 14px;
            opacity: 0.8;
            margin-top: 15px;
        }
        .instructions {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin: 25px 0;
        }
        .instructions h3 {
            color: #2c3e50;
            margin-top: 0;
            font-size: 18px;
        }
        .instructions ol {
            margin: 15px 0;
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 8px;
            color: #555;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .warning strong {
            color: #d63031;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .security-note {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">🚀 Job Platform</div>
            <h1 class="title">Code de réinitialisation</h1>
        </div>

        <!-- Greeting -->
        <div class="greeting">
            Bonjour {{ $user->name ?? 'Utilisateur' }},
        </div>

        <!-- Main content -->
        <p>Vous avez demandé la réinitialisation de votre mot de passe. Voici votre code de vérification :</p>

        <!-- Code section -->
        <div class="code-section">
            <div class="code-label">Votre code de vérification</div>
            <div class="reset-code">{{ $resetCode }}</div>
            <div class="code-info">
                ⏰ Ce code expire dans <strong>15 minutes</strong>
            </div>
        </div>

        <!-- Instructions -->
        <div class="instructions">
            <h3>📋 Comment utiliser ce code :</h3>
            <ol>
                <li>Retournez sur la page de connexion</li>
                <li>Cliquez sur "Mot de passe oublié"</li>
                <li>Saisissez ce code à 6 chiffres</li>
                <li>Créez votre nouveau mot de passe</li>
            </ol>
        </div>

        <!-- Warning -->
        <div class="warning">
            <strong>⚠️ Important :</strong> Si vous n'avez pas demandé cette réinitialisation, ignorez cet email. 
            Votre mot de passe actuel reste inchangé.
        </div>

        <!-- Security note -->
        <div class="security-note">
            🔒 <strong>Note de sécurité :</strong> Ce code ne peut être utilisé qu'une seule fois et expire automatiquement 
            après 15 minutes pour votre sécurité.
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>© {{ date('Y') }} Job Platform - Plateforme de gestion d'offres d'emploi</p>
        </div>
    </div>
</body>
</html>
