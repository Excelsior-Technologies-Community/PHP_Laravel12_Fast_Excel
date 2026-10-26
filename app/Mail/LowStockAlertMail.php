<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $lowStockCount,
        public int $threshold,
        public ?string $excelFilePath = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚨 [Low Stock Alert] {$this->lowStockCount} Products Need Reordering (Stock <= {$this->threshold})",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family:sans-serif; max-width:600px; margin:auto; padding:24px; border:1px solid #e5e7eb; border-radius:12px;'>
                    <h2 style='color:#dc2626; margin-top:0;'>⚠️ Urgent Low Stock Alert</h2>
                    <p style='color:#374151; font-size:15px; line-height:1.6;'>
                        Automated inventory monitor has detected that <strong>{$this->lowStockCount} products</strong> have reached or fallen below the minimum stock threshold of <strong>{$this->threshold} units</strong>.
                    </p>
                    <p style='color:#4b5563; font-size:14px;'>
                        The complete itemized list of affected products, categories, SKU codes, and current quantities has been exported and attached to this email as an Excel spreadsheet for immediate replenishment and vendor PO generation.
                    </p>
                    <div style='margin:20px 0; padding:16px; background:#fef2f2; border-left:4px solid #ef4444; border-radius:6px;'>
                        <span style='font-weight:bold; color:#991b1b;'>Action Required:</span>
                        <span style='color:#b91c1c;'> Please review the attached spreadsheet and issue reorders.</span>
                    </div>
                    <hr style='border:none; border-top:1px solid #f3f4f6; margin:20px 0;'>
                    <small style='color:#9ca3af;'>Laravel 12 FastExcel Autonomous Stock Telemetry</small>
                </div>
            "
        );
    }

    public function attachments(): array
    {
        if ($this->excelFilePath && file_exists($this->excelFilePath)) {
            return [
                Attachment::fromPath($this->excelFilePath)
                    ->as('Low_Stock_Report_' . now()->format('Y-m-d') . '.xlsx')
                    ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ];
        }

        return [];
    }
}
