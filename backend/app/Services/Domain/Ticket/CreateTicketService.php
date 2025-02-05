<?php

namespace HiEvents\Services\Domain\Ticket;

use Exception;
use HiEvents\DomainObjects\TicketDomainObject;
use HiEvents\Helper\DateHelper;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Repository\Interfaces\TicketRepositoryInterface;
use HiEvents\Services\Domain\Tax\DTO\TaxAndTicketAssociateParams;
use HiEvents\Services\Domain\Tax\TaxAndTicketAssociationService;
use HTMLPurifier;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Throwable;

class CreateTicketService
{
    public function __construct(
        private readonly TicketRepositoryInterface      $ticketRepository,
        private readonly DatabaseManager                $databaseManager,
        private readonly TaxAndTicketAssociationService $taxAndTicketAssociationService,
        private readonly TicketPriceCreateService       $priceCreateService,
        private readonly HTMLPurifier                   $purifier,
        private readonly EventRepositoryInterface       $eventRepository,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function createTicket(TicketDomainObject $ticketData): TicketDomainObject
    {
        return $this->databaseManager->transaction(function () use ($ticketData) {
            $ticket = $this->persistTicket($ticketData);

            if ($ticketData->getTaxAndFeeIds()) {
                $this->associateTaxesAndFees($ticket, $ticketData->getTaxAndFeeIds(), $ticketData->getAccountId());
            }

            return $this->createTicketPrices($ticket, $ticketData);
        });
    }

    private function persistTicket(TicketDomainObject $ticketsData): TicketDomainObject
    {
        $event = $this->eventRepository->findById($ticketsData->getEventId());

        $attributes = [
            'title' => $ticketsData->getTitle() ?? '',
            'type' => $ticketsData->getType(),
            'order' => $ticketsData->getOrder(),
            'sale_start_date' => $ticketsData->getSaleStartDate()
                ? DateHelper::convertToUTC($ticketsData->getSaleStartDate(), $event->getTimezone())
                : null,
            'sale_end_date' => $ticketsData->getSaleEndDate()
                ? DateHelper::convertToUTC($ticketsData->getSaleEndDate(), $event->getTimezone())
                : null,
            'max_per_order' => $ticketsData->getMaxPerOrder() ?? 100,
            'description' => $this->purifier->purify($ticketsData->getDescription() ?? ''),
            'min_per_order' => $ticketsData->getMinPerOrder() ?? 1,
            'is_hidden' => $ticketsData->getIsHidden() ?? false,
            'hide_before_sale_start_date' => $ticketsData->getHideBeforeSaleStartDate() ?? false,
            'hide_after_sale_end_date' => $ticketsData->getHideAfterSaleEndDate() ?? false,
            'hide_when_sold_out' => $ticketsData->getHideWhenSoldOut() ?? false,
            'show_quantity_remaining' => $ticketsData->getShowQuantityRemaining() ?? false,
            'is_hidden_without_promo_code' => $ticketsData->getIsHiddenWithoutPromoCode() ?? false,
            'event_id' => $ticketsData->getEventId(),
            'position' => $ticketsData->getPosition(),
            'seat_number' => $ticketsData->getSeatNumber(),
            'section' => $ticketsData->getSection(),
        ];

        // Debug para ver qué datos estamos intentando insertar
        \Log::info('Ticket attributes:', $attributes);

        // Asegurarnos de que todos los campos requeridos estén presentes
        if (empty($attributes['title'])) {
            throw new \InvalidArgumentException('Title is required');
        }

        return $this->ticketRepository->create($attributes);
    }

    /**
     * @throws Exception
     */
    private function createTicketTaxesAndFees(
        TicketDomainObject $ticket,
        array              $taxAndFeeIds,
        int                $accountId,
    ): Collection
    {
        return $this->taxAndTicketAssociationService->addTaxesToTicket(
            new TaxAndTicketAssociateParams(
                ticketId: $ticket->getId(),
                accountId: $accountId,
                taxAndFeeIds: $taxAndFeeIds,
            ),
        );
    }

    /**
     * @throws Exception
     */
    private function associateTaxesAndFees(TicketDomainObject $persistedTicket, array $taxAndFeeIds, int $accountId): void
    {
        $persistedTicket->setTaxAndFees($this->createTicketTaxesAndFees(
            ticket: $persistedTicket,
            taxAndFeeIds: $taxAndFeeIds,
            accountId: $accountId,
        ));
    }

    private function createTicketPrices(TicketDomainObject $persistedTicket, TicketDomainObject $ticket): TicketDomainObject
    {
        $prices = $this->priceCreateService->createPrices(
            ticketId: $persistedTicket->getId(),
            prices: $ticket->getTicketPrices(),
            event: $this->eventRepository->findById($ticket->getEventId()),
        );

        return $persistedTicket->setTicketPrices($prices);
    }
}
