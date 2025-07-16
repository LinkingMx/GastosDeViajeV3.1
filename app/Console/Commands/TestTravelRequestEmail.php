<?php

namespace App\Console\Commands;

use App\Models\TravelRequest;
use App\Models\User;
use App\Mail\TravelRequestCreated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestTravelRequestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:travel-request-email {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test travel request email functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado.");
            return;
        }

        // Buscar una solicitud existente del usuario o crear una de prueba
        $travelRequest = TravelRequest::where('user_id', $user->id)->first();
        
        if (!$travelRequest) {
            $this->error("No se encontró ninguna solicitud de viaje para el usuario {$user->name}.");
            $this->info("Crea una solicitud primero desde la interfaz web.");
            return;
        }

        $this->info("Enviando email de prueba a: {$user->email}");
        $this->info("Solicitud de viaje: {$travelRequest->folio}");

        try {
            Mail::to($user->email)->send(new TravelRequestCreated($travelRequest));
            $this->info("✅ Email enviado exitosamente!");
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar email: " . $e->getMessage());
        }
    }
}