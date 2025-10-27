<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use App\Enums\HttpStatusCode;
use Exception;

class CustomApiException extends Exception
{
    protected ErrorCode $errorCode;
    protected HttpStatusCode $httpStatusCode;
    protected array $context;

    public function __construct(
        ErrorCode $errorCode,
        HttpStatusCode $httpStatusCode,
        string $message = null,
        array $context = [],
        \Throwable $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->httpStatusCode = $httpStatusCode;
        $this->context = $context;

        $message = $message ?? $this->getDefaultMessage();

        parent::__construct($message, $httpStatusCode->value, $previous);
    }

    public function getErrorCode(): ErrorCode
    {
        return $this->errorCode;
    }

    public function getHttpStatusCode(): HttpStatusCode
    {
        return $this->httpStatusCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    protected function getDefaultMessage(): string
    {
        return match($this->errorCode) {
            ErrorCode::COMPTE_NOT_FOUND => 'Le compte bancaire demandé n\'a pas été trouvé.',
            ErrorCode::COMPTE_STATUS_INVALID => 'Le statut du compte n\'est pas valide pour ce type d\'opération.',
            ErrorCode::COMPTE_CREATION_FAILED => 'La création du compte bancaire a échoué.',
            ErrorCode::COMPTE_UPDATE_FAILED => 'La mise à jour du compte bancaire a échoué.',
            ErrorCode::COMPTE_ARCHIVE_FAILED => 'L\'archivage du compte bancaire a échoué.',
            ErrorCode::CLIENT_NOT_FOUND => 'Le client demandé n\'a pas été trouvé.',
            ErrorCode::CLIENT_CREATION_FAILED => 'La création du client a échoué.',
            ErrorCode::VALIDATION_ERROR => 'Les données fournies ne sont pas valides.',
            ErrorCode::UNAUTHORIZED => 'Accès non autorisé.',
            ErrorCode::FORBIDDEN => 'Accès refusé.',
            ErrorCode::INTERNAL_SERVER_ERROR => 'Une erreur interne du serveur s\'est produite.',
            ErrorCode::DATABASE_ERROR => 'Une erreur de base de données s\'est produite.',
            ErrorCode::INVALID_REQUEST => 'La requête n\'est pas valide.',
        };
    }

    public function toArray(): array
    {
        return [
            'success' => false,
            'error' => [
                'code' => $this->errorCode->value,
                'message' => $this->getMessage(),
                'context' => $this->context,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}