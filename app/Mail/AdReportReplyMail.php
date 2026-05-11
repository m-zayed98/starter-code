<?php

namespace App\Mail;

use App\Models\AdReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdReportReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AdReport $report
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Reply to your ad report')
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ad_report_reply'
        );
    }
}
