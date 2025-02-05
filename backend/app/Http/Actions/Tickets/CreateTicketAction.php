<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\Tickets;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Exceptions\InvalidTaxOrFeeIdException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\Request\Ticket\UpsertTicketRequest;
use HiEvents\Http\ResponseCodes;
use HiEvents\Resources\Ticket\TicketResource;
use HiEvents\Services\Handlers\Ticket\CreateTicketHandler;
use HiEvents\Services\Handlers\Ticket\DTO\UpsertTicketDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;
use HiEvents\DomainObjects\Enums\TicketType;
use HiEvents\Services\Domain\Ticket\DTO\TicketPriceDTO;

class CreateTicketAction extends BaseAction
{
    private CreateTicketHandler $createTicketHandler;

    public function __construct(CreateTicketHandler $handler)
    {
        $this->createTicketHandler = $handler;
    }

    /**
     * @throws Throwable
     */
    public function __invoke(int $eventId, UpsertTicketRequest $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $accountId = $this->getAuthenticatedAccountId();
        
        // Debug para ver qué valores tenemos
        \Log::info('Creating ticket with:', [
            'eventId' => $eventId,
            'accountId' => $accountId
        ]);

        if (!$accountId) {
            throw new \RuntimeException('No authenticated account found');
        }

        $request->merge([
            'event_id' => $eventId,
            'account_id' => $accountId,
        ]);

        try {
            $type = match ($request->get('type')) {
                'PAID' => TicketType::PAID,
                'FREE' => TicketType::FREE,
                'DONATION' => TicketType::DONATION,
                default => throw new \InvalidArgumentException('Invalid ticket type')
            };

            // Convertir los precios a DTOs
            $prices = collect($request->get('prices'))->map(function ($price) {
                return new TicketPriceDTO(
                    price: $price['price'] ?? 0.00,
                    label: $price['label'] ?? null,
                    sale_start_date: $price['sale_start_date'] ?? null,
                    sale_end_date: $price['sale_end_date'] ?? null,
                    initial_quantity_available: $price['initial_quantity_available'] ?? null,
                    is_hidden: $price['is_hidden'] ?? false
                );
            });

            $ticketData = new UpsertTicketDTO(
                account_id: (int) $accountId,
                event_id: (int) $eventId,
                title: $request->get('title'),
                type: $type,
                prices: $prices,  // Usar la colección de DTOs
                price: $request->get('price', 0.00),
                order: $request->get('order', 1),
                initial_quantity_available: $request->get('initial_quantity_available'),
                quantity_sold: $request->get('quantity_sold', 0),
                sale_start_date: $request->get('sale_start_date'),
                sale_end_date: $request->get('sale_end_date'),
                max_per_order: $request->get('max_per_order', 100),
                description: $request->get('description'),
                min_per_order: $request->get('min_per_order', 0),
                is_hidden: $request->get('is_hidden', false),
                hide_before_sale_start_date: $request->get('hide_before_sale_start_date', false),
                hide_after_sale_end_date: $request->get('hide_after_sale_end_date', false),
                hide_when_sold_out: $request->get('hide_when_sold_out', false),
                show_quantity_remaining: $request->get('show_quantity_remaining', false),
                is_hidden_without_promo_code: $request->get('is_hidden_without_promo_code', false),
                tax_and_fee_ids: $request->get('tax_and_fee_ids', []),
                ticket_id: $request->get('ticket_id'),
                position: $request->get('position'),
                seat_number: $request->get('seat_number'),
                section: $request->get('section')
            );

            $ticket = $this->createTicketHandler->handle($ticketData);
        } catch (InvalidTaxOrFeeIdException $e) {
            throw ValidationException::withMessages([
                'tax_and_fee_ids' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            resource: TicketResource::class,
            data: $ticket,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
