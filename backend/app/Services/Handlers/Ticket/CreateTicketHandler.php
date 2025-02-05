<?php

declare(strict_types=1);

namespace HiEvents\Services\Handlers\Ticket;

use HiEvents\DomainObjects\Enums\TicketType;
use HiEvents\DomainObjects\Generated\TicketPriceDomainObjectAbstract;
use HiEvents\DomainObjects\TicketDomainObject;
use HiEvents\DomainObjects\TicketPriceDomainObject;
use HiEvents\Services\Domain\Ticket\CreateTicketService;
use HiEvents\Services\Domain\Ticket\DTO\TicketPriceDTO;
use HiEvents\Services\Handlers\Ticket\DTO\UpsertTicketDTO;
use Throwable;

class CreateTicketHandler
{
    public function __construct(
        private readonly CreateTicketService $ticketCreateService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(UpsertTicketDTO $ticketsData): TicketDomainObject
    {
        // Debug para ver qué datos llegan del DTO
        \Log::info('DTO data:', [
            'title' => $ticketsData->title,
            'type' => $ticketsData->type, 
            'position' => $ticketsData->position, 
            'seat_number' => $ticketsData->seat_number,
        ]);

        $ticketPrices = $ticketsData->prices->map(fn(TicketPriceDTO $price) => TicketPriceDomainObject::hydrateFromArray([
            TicketPriceDomainObjectAbstract::PRICE => $ticketsData->type === TicketType::FREE ? 0.00 : $price->price,
            TicketPriceDomainObjectAbstract::LABEL => $price->label,
            TicketPriceDomainObjectAbstract::SALE_START_DATE => $price->sale_start_date,
            TicketPriceDomainObjectAbstract::SALE_END_DATE => $price->sale_end_date,
            TicketPriceDomainObjectAbstract::INITIAL_QUANTITY_AVAILABLE => $price->initial_quantity_available,
            TicketPriceDomainObjectAbstract::IS_HIDDEN => $price->is_hidden,
        ]));

        $ticket = (new TicketDomainObject())
            ->setTitle($ticketsData->title)
            ->setType($ticketsData->type->name)
            ->setOrder($ticketsData->order)
            ->setSaleStartDate($ticketsData->sale_start_date)
            ->setSaleEndDate($ticketsData->sale_end_date)
            ->setMaxPerOrder($ticketsData->max_per_order)
            ->setDescription($ticketsData->description)
            ->setMinPerOrder($ticketsData->min_per_order)
            ->setIsHidden($ticketsData->is_hidden)
            ->setHideBeforeSaleStartDate($ticketsData->hide_before_sale_start_date)
            ->setHideAfterSaleEndDate($ticketsData->hide_after_sale_end_date)
            ->setHideWhenSoldOut($ticketsData->hide_when_sold_out)
            ->setShowQuantityRemaining($ticketsData->show_quantity_remaining)
            ->setIsHiddenWithoutPromoCode($ticketsData->is_hidden_without_promo_code)
            ->setTicketPrices($ticketPrices)
            ->setEventId($ticketsData->event_id)
            ->setPosition($ticketsData->position)
            ->setSeatNumber($ticketsData->seat_number)
            ->setSection($ticketsData->section)
            ->setTaxAndFeeIds($ticketsData->tax_and_fee_ids)
            ->setAccountId($ticketsData->account_id);

        return $this->ticketCreateService->createTicket(
            $ticket,
            $ticketsData->account_id,
            $ticketsData->tax_and_fee_ids
        );
    }
}
