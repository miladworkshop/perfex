<?php

namespace app\services\ai\Contracts;

use app\services\ai\Data\Ticket;

interface AiTicketInterface
{
    public function __construct(AiProviderInterface $aiProvider);

    public function summarizeTicket(Ticket $ticket): string;

    public function suggestTicketReply(Ticket $ticket): string;
}
