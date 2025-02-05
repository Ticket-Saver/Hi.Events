<?php

namespace HiEvents\DomainObjects\Enums;

enum TicketType
{
    use BaseEnum;

    case FREE;
    case PAID;
    case DONATION;
    case TIERED;
    case REGISTRATION;
}
