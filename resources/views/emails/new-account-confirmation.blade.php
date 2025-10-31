<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de création de compte</title>
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
            background-color: #28a745;
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
        .account-info {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-item {
            margin: 10px 0;
            padding: 8px;
            background-color: #e9ecef;
            border-radius: 3px;
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
        <h1>✅ Nouveau compte bancaire créé</h1>
        <p>Confirmation de création de compte</p>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $client->user->prenom }} {{ $client->user->nom }}</strong>,</p>

        <p>Nous avons le plaisir de vous confirmer la création de votre nouveau compte bancaire dans notre système de gestion.</p>

        <h3>📋 Détails du nouveau compte</h3>

        <div class="account-info">
            <div class="info-item">
                <strong>Numéro de compte :</strong> {{ $compte->numeroCompte }}
            </div>
            <div class="info-item">
                <strong>Type de compte :</strong> {{ $compte->type === 'epargne' ? 'Épargne' : 'Chèque' }}
            </div>
            <div class="info-item">
                <strong>Solde initial :</strong> {{ number_format($compte->solde, 0, ',', ' ') }} FCFA
            </div>
            <div class="info-item">
                <strong>Date de création :</strong> {{ $compte->created_at->format('d/m/Y') }}
            </div>
            <div class="info-item">
                <strong>Statut :</strong> Actif
            </div>
        </div>

        <p>Vous pouvez maintenant utiliser ce compte pour :</p>
        <ul>
            <li>Effectuer des dépôts et retraits</li>
            <li>Réaliser des transferts</li>
            <li>Consulter votre historique de transactions</li>
            <li>Gérer vos opérations bancaires en ligne</li>
        </ul>

        <p>Si vous avez des questions concernant ce nouveau compte ou si vous souhaitez obtenir plus d'informations sur nos services, n'hésitez pas à nous contacter.</p>

        <p>Cordialement,<br>
        L'équipe Gestion Comptes</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>&copy; {{ date('Y') }} Gestion Comptes. Tous droits réservés.</p>
    </div>
</body>
</html>