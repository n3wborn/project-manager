<?php

namespace App\Helper;

final class ApiMessages
{
    public const PROJECT_CREATE_ERROR_MESSAGE = 'An error occurred while persisting project';
    public const PROJECT_CREATE_SUCCESS_MESSAGE = 'Le project a bien été créé';
    public const PROJECT_DELETE_ERROR_MESSAGE = 'An error occurred during project removal';
    public const PROJECT_DELETE_SUCCESS_MESSAGE = 'Le projet a bien été supprimé';
    public const PROJECT_UPDATE_ERROR_MESSAGE = 'An error occurred during project update';
    public const PROJECT_UPDATE_SUCCESS_MESSAGE = 'Le projet a bien été mis à jour';
    public const PROJECT_NAME_UNAVAILABLE = 'Project name already exists';
    public const PROJECT_NOT_FOUND = 'Project not found';
    public const PROJECT_UNKNOWN = 'Project not found';
    public const DEFAULT_ERROR_MESSAGE = 'Oops, an error occured...';
    public const EMAIL_ADDRESS_UNKNOWN = "Aucun utilisateur avec cette adresse mail n'a été trouvé";
    public const ERROR_EMPTY_PASSWORD_TERMS = 'The presented password cannot be empty.';
    public const ERROR_MAIL_TERMS = 'Bad credentials.';
    public const ERROR_PASSWORD_TERMS = 'The presented password is invalid.';
    public const FETCHING_USER_PROFILE_ERROR_MESSAGE = 'An error occurred while fetching user profile';
    public const INDEX_ERROR = 'error';
    public const INDEX_MESSAGE = 'message';
    public const INDEX_STATUS = 'status';
    public const INDEX_SUCCESS = 'success';
    public const INDEX_WARNING = 'warning';
    public const MESSAGE_OK = 'OK';

    public static function translate(string $key): string
    {
        return self::TRANSLATIONS[$key] ?? $key;
    }

    public const TRANSLATIONS = [
        self::PROJECT_UPDATE_ERROR_MESSAGE => 'Oops, une erreur est survenue lors de la modification du projet',
        self::ERROR_EMPTY_PASSWORD_TERMS => 'Le mot de passe est nécessaire',
        self::ERROR_MAIL_TERMS => 'Votre mot de passe ou votre adresse mail est incorrect',
        self::ERROR_PASSWORD_TERMS => 'Votre mot de passe ou votre adresse mail est incorrect',
        self::FETCHING_USER_PROFILE_ERROR_MESSAGE => 'Oops, une erreur est survenue durant la récupération de vos données',
    ];
}