<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ZamowienieMail extends Mailable
{
    use Queueable, SerializesModels;

    public $zamowienie;

    public function __construct($zamowienie)
    {
        $this->zamowienie = $zamowienie;
    }

    public function build()
    {
        return $this->subject("Zamówienie #{$this->zamowienie->id}")
                    ->view('emails.zamowienie')
                    ->with(['zamowienie' => $this->zamowienie]);
    }
}
