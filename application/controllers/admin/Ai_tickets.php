<?php

use app\services\ai\AiProviderRegistry;
use app\services\ai\AiTicket;
use app\services\ai\Contracts\AiTicketInterface;
use app\services\ai\Data\Ticket;

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read Tickets_model $tickets_model
 */
class Ai_tickets extends AdminController
{
    private AiTicketInterface $aiTicket;

    public function __construct()
    {
        parent::__construct();

        if (staff_cant('view', '', 'tickets')) {
            access_denied('tickets');
        }

        try {
            $provider       = AiProviderRegistry::getProvider(get_option('ai_provider'));
            $this->aiTicket = new AiTicket($provider);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);

            return;
        }
        $this->load->model('tickets_model');
    }

    public function summarize_ticket($ticketId): void
    {
        if (get_option('ai_enable_ticket_summarization') == 0) {
            show_404('Ticket summarization is disabled');
        }

        try {
            $summary = $this->aiTicket->summarizeTicket($this->prepareTicket($ticketId));

            echo json_encode([
                'success' => true,
                'message' => $summary,
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function suggest_reply($ticketId): void
    {
        if (get_option('ai_enable_ticket_reply_suggestions') == 0) {
            show_404(_l('Ticket Reply suggestion is disabled'));
        }

        try {
            $reply = $this->aiTicket->suggestTicketReply($this->prepareTicket($ticketId));

            echo json_encode([
                'success' => true,
                'message' => $reply,
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function prepareTicket($ticketId): Ticket
    {
        $ticket = $this->tickets_model->get($ticketId);
        if (! $ticket) {
            show_404('Ticket not found');
        }

        $ticket  = new Ticket($ticketId, $ticket->subject, $ticket->message);
        $replies = $this->tickets_model->get_ticket_replies($ticketId);

        $ticket->setReplies(
            collect($replies)->map(fn ($r) => ['user' => $r['admin'] == 1 ? 'admin' : 'contact', 'message' => $r['message']])->toArray()
        );

        return $ticket;
    }
}
