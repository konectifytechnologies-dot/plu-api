<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminderEmail;
use App\Models\Invoice;
use App\Models\PaymentReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Resend\Laravel\Facades\Resend;


class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-invoice-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send rent payment reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $today = Carbon::parse('2026-02-27'); //now()->startOfDay();
         $reminders = [];
         Invoice::with('reminders')->where('status', 'pending')
                  ->chunkById(100, function ($invoices) use ($today, &$reminders) {
                        foreach ($invoices as $invoice){
                            $daysUntilDue = $today->diffInDays($invoice->due_date, false);
                            $reminderType = match ((int) $daysUntilDue) {
                                            7 => '7_days',
                                            2 => '2_days',
                                            1 => '1_day',
                                            default => null,
                            };
                            if (!$reminderType) {
                                continue;
                            }
                            $alreadySent = $invoice->reminders()
                                        ->where('type', $reminderType)
                                        ->exists();
                            if ($alreadySent) {
                                $this->info($alreadySent, );
                                continue;
                            }
                            $sent = Resend::emails()->send([
                                        'from' => 'Disqav <shops@disqav.com>',
                                        'to' => $invoice->invoice_reminder_data['email'],
                                        'subject' => 'Rent Payment Reminder',
                                        'html' => (new PaymentReminderEmail($invoice->invoice_reminder_data))->render(),
                                    ]);
                            $reminders[] = [
                                'id'=>Str::ulid(),
                                'invoice_id'=>$invoice->id,
                                'type'=>$reminderType,
                                'sent_at'=>now(),
                                'created_at'=>now(),
                                'updated_at'=>now(),
                            ];
                        }
            });
            PaymentReminder::insert($reminders);
            $this->info('Monthly invoices generated', );
            return Command::SUCCESS;
    }
}
