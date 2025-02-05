<?php

namespace HiEvents\Services\Domain\Ticket\DTO;

use HiEvents\Services\Handlers\Ticket\DTO\UpsertTicketDTO;

class CreateTicketDTO extends UpsertTicketDTO
{
    public function __construct(
        // ... otros campos ...
        public readonly ?string $section = null,
    ) {
    }
}
