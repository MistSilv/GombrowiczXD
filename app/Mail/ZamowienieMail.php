<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ZamowienieMail extends Mailable
{
    use Queueable, SerializesModels;

    public $xlsxContent;
    public $csvContent;
    public $zamowienie;
    public $zamowienieId;

    public function __construct($xlsxContent, $csvContent, $zamowienie)
    {
        $this->xlsxContent = $xlsxContent;
        $this->csvContent = $csvContent;
        $this->zamowienie = $zamowienie;
        $this->zamowienieId = $zamowienie->id;
    }

    public function build()
    {
        return $this->subject("Zamówienie #{$this->zamowienieId}")
            ->view('emails.zamowienie', ['zamowienie' => $this->zamowienie])
            ->attachData($this->xlsxContent, "zamowienie_{$this->zamowienieId}.xlsx", [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->attachData($this->csvContent, "zamowienie_{$this->zamowienieId}.csv", [
                'mime' => 'text/csv',
            ]);
    }
}
