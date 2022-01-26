<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $records = $this->data['stats_data'];
        $header = $this->data['header'];
//        $locations = $this->data['locations'];
        $reportHandler = $this->data['reportHandler'];
        return $this->subject($this->data['subject'])->view('email.report', compact('records', 'header', 'reportHandler'));
    }
}
