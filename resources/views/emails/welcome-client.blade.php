<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue chez Gestion Comptes</title>
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
        .credentials {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .credential-item {
            margin: 10px 0;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 3px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏦 Bienvenue chez Gestion Comptes</h1>
        <p>Votre compte bancaire a été créé avec succès !</p>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $user->nom }}</strong>,</p>

        <p>Nous sommes ravis de vous accueillir dans notre système de gestion bancaire. Votre compte a été créé et vous pouvez maintenant accéder à vos services bancaires en ligne.</p>

        <h3>🔐 Vos identifiants de connexion</h3>

        <div class="credentials">
            <div class="credential-item">
                <strong>Login :</strong> {{ $login }}
            </div>
            <div class="credential-item">
                <strong>Mot de passe :</strong> {{ $password }}
            </div>
        </div>

        <div class="warning">
            <strong>⚠️ Important :</strong> Veuillez noter ces identifiants et les conserver en lieu sûr. Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe lors de votre première connexion.
        </div>

        <p>Vous pouvez maintenant :</p>
        <ul>
            <li>Consulter vos comptes bancaires</li>
            <li>Effectuer des opérations bancaires</li>
            <li>Gérer vos informations personnelles</li>
            <li>Accéder à votre historique de transactions</li>
        </ul>

        <p>Si vous avez des questions ou besoin d'assistance, n'hésitez pas à nous contacter.</p>

        <p>Cordialement,<br>
        L'équipe Gestion Comptes</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>&copy; {{ date('Y') }} Gestion Comptes. Tous droits réservés.</p>
    </div>
</body>
</html>