<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ZamowienieMail extends Mailable
{
    use Queueable, SerializesModels;

    public $xlsxContent;
    public $zamowienie;
    public $zamowienieId;

    public function __construct($xlsxContent, $zamowienie)
    {
        $this->xlsxContent = $xlsxContent;
        $this->zamowienie = $zamowienie;
        $this->zamowienieId = $zamowienie->id;
    }

    public function build()
    {
        $dateTime = now()->format('Y-m-d H:i');

        return $this->subject("Zamówienie #{$this->zamowienieId}")
            ->view('emails.zamowienie', ['zamowienie' => $this->zamowienie])
            ->attachData($this->xlsxContent, "zamowienie_{$dateTime}.xlsx", [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}
