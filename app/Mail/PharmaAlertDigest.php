<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PharmaAlertDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $lowStock,
        public array $expiringBatches,
        public array $overduePurchaseOrders,
        public array $overdueInvoices,
    ) {
    }

    public function build()
    {
        return $this
            ->subject('LeafLight Pharma — Daily Stock & Account Alerts')
            ->view('emails.pharma-alert-digest');
    }
}
