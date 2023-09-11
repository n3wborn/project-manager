<?php

namespace App\Service\Project;

use App\Controller\ProjectController;
use App\Entity\Project;
use App\Exception\NotFoundException;
use App\Helper\ApiMessages;
use Symfony\Component\HttpFoundation\Request;

final class Helper
{
    public static function isEditRoute(Request $request): bool
    {
        return ProjectController::ROUTE_EDIT === $request->get('_route');
    }

    public static function generateEditSuccessMessage(Request $request): string
    {
        return ProjectController::ROUTE_EDIT === $request->get('_route')
            ? ApiMessages::PROJECT_UPDATE_SUCCESS_MESSAGE
            : ApiMessages::PROJECT_CREATE_SUCCESS_MESSAGE;
    }

    /** @throws NotFoundException  */
    public static function validateRequestResource(Request $request, Project $project): void
    {
        (self::isEditRoute($request) && (null === $project))
            && throw new NotFoundException(ApiMessages::translate(ApiMessages::PROJECT_UNKNOWN));
    }
}
