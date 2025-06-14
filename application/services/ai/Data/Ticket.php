<?php

namespace app\services\ai\Data;

use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\HtmlConverter;

class Ticket
{
    private array $replies = [];

    public function __construct(
        public readonly int    $id,
        public readonly string $subject,
        public readonly string $message,

    )
    {
    }

    /**
     *
     * @param array<int, array{'message': string}> $replies An array of replies where each reply is an associative array with keys as sender and values as their messages.
     * Example: [
     *     ['message' => 'This is the first reply.', '],
     *     ['Jane Smith' => 'This is the second reply.']
     * ]
     */
    public function setReplies(array $replies): void
    {
        $this->replies = $replies;
    }

    public function asAIText(): string
    {
        $converter = new HtmlConverter();
        $converter->getConfig()->setOption('strip_tags', true);
        $converter->getEnvironment()->addConverter(new TableConverter());


        return <<<TICKET
ID: $this->id
SUBJECT: $this->subject
MESSAGE: ```{$converter->convert($this->message)}```
CONVERSATION History:
```{$converter->convert($this->generateRepliesTable())}```
TICKET;

    }


    private function generateRepliesTable(): string
    {
        $tableRows = '<tr><th>From</th><th>Message</th></tr>';
        foreach ($this->replies as $reply) {
            $tableRows .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $reply['user'], $reply['message']);
        }
        return '<table>' . $tableRows . '</table>';
    }

}