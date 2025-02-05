<?php

namespace HiEvents\Http\Actions\Events;

use HiEvents\Exceptions\OrganizerNotFoundException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\Request\Event\CreateEventRequest;
use HiEvents\Resources\Event\EventResource;
use HiEvents\Services\Handlers\Event\CreateEventHandler;
use HiEvents\Services\Handlers\Event\DTO\CreateEventDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateEventAction extends BaseAction
{
    public function __construct(
        private readonly CreateEventHandler $createEventHandler
    )
    {
    }

    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(CreateEventRequest $request): JsonResponse
    {
        $authorisedUser = $this->getAuthenticatedUser();
        $accountId = $this->getAuthenticatedAccountId();

        $eventData = array_merge(
            $request->validated(),
            [
                'account_id' => $accountId,
                'user_id' => $authorisedUser->getId(),
                'tipoticket' => $request->input('tipoticket'),
                'map' => $request->input('map'),
                'timezone' => $request->input('timezone', config('app.timezone', 'UTC')),
            ]
        );

        try {
            $event = $this->createEventHandler->handle(
                eventData: CreateEventDTO::fromArray($eventData)
            );
        } catch (OrganizerNotFoundException $e) {
            throw ValidationException::withMessages([
                'organizer_id' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(EventResource::class, $event);
    }
}
