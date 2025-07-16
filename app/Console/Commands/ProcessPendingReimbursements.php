<?php

namespace App\Console\Commands;

use App\Models\ExpenseVerification;
use Illuminate\Console\Command;

class ProcessPendingReimbursements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reimbursements:process-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process approved expense verifications and mark those needing reimbursement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Procesando comprobaciones aprobadas para detectar necesidad de reembolso...');

        // Encontrar comprobaciones aprobadas que no tienen estado de reembolso
        $verifications = ExpenseVerification::where('status', 'approved')
            ->whereNull('reimbursement_status')
            ->with(['travelRequest', 'receipts'])
            ->get();

        $processed = 0;
        $markedForReimbursement = 0;

        foreach ($verifications as $verification) {
            if ($verification->needsReimbursement()) {
                $verification->markForReimbursement();
                $markedForReimbursement++;
                
                $this->line("✓ Marcada para reembolso: {$verification->folio} - Monto: $" . 
                          number_format($verification->getReimbursementAmountNeeded(), 2));
            }
            $processed++;
        }

        $this->info("Procesamiento completado:");
        $this->line("- Comprobaciones procesadas: {$processed}");
        $this->line("- Marcadas para reembolso: {$markedForReimbursement}");

        return Command::SUCCESS;
    }
}
