<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynamicTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $subjectText,
        private readonly string $htmlBody
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->subjectText)->html($this->htmlBody);
    }
}
