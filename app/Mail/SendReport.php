<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendReport extends Mailable
{
    use Queueable, SerializesModels;

    private $args;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($args)
    {
        $this->args = $args;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->args['from'],'FACTURAZZION®')
            ->subject($this->args['subject'])
            ->markdown('emails.sendQuotation')
            ->attachData($this->args['pdf_content'], $this->args['pdf_filename'], [
                    'mime' => 'application/pdf',
                ])
            ->attachData($this->args['xls_content'], $this->args['xls_filename'], [
                    'mime' => 'application/vnd.ms-excel',
                ])
            ->with([
                'subject' => $this->args['subject'],
                'message_body' => $this->args['message_body']
            ]);
    }
}
