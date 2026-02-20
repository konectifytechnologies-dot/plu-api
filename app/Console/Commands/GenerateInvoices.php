<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly rent invoices';

    /**
     * Execute the console command.
     */
    public function handle(InvoiceService $invoice)
    {
        $invoice->generateTenantInvoices();
        $this->info('Monthly invoices generated');
        return Command::SUCCESS;
    }
}
