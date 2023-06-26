<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCfdi extends Mailable
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
            // ->attach(url('/invoice/'.$this->args['quotation_pdf'].'/print_quot'))
            ->attachData($this->args['pdf_content'], $this->args['quotation_pdf'], [
                    'mime' => 'application/pdf',
                ])
            ->attachData($this->args['xml_content'], $this->args['quotation_xml'], [
                    'mime' => 'text/mxl',
                ])
            ->with([
                'subject' => $this->args['subject'],
                'message_body' => $this->args['message_body']
            ]);
    }
}
