<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ZamowienieMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $xlsxContent;
    public $zamowienie;
    public $zamowienieId;

    public function __construct($base64XlsxContent, $zamowienie)
    {
        $this->xlsxContent = $base64XlsxContent; 
        $this->zamowienie = $zamowienie;
        $this->zamowienieId = $zamowienie->id;
    }

    public function build()
    {
        $dateTime = now()->format('Y-m-d_H:i');

        return $this->subject("Zamówienie #{$this->zamowienieId}")
            ->view('emails.zamowienie', ['zamowienie' => $this->zamowienie])
            ->attachData(base64_decode($this->xlsxContent), "zamowienie_{$dateTime}.xlsx", [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}
