<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tipos de documentos por defecto
        $defaultTypes = [
            [
                'name' => 'Reserva de Hotel',
                'slug' => 'hotel_reservation',
                'description' => 'Comprobantes de reservas de hotel para el viaje',
                'icon' => 'heroicon-o-home',
                'color' => 'blue',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Boleto de Avión',
                'slug' => 'flight_ticket',
                'description' => 'Boletos de avión, boarding passes y confirmaciones de vuelo',
                'icon' => 'heroicon-o-paper-airplane',
                'color' => 'green',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Comprobante de Transporte',
                'slug' => 'transport_receipt',
                'description' => 'Recibos de taxi, uber, transporte público, etc.',
                'icon' => 'heroicon-o-truck',
                'color' => 'yellow',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Otro Documento',
                'slug' => 'other_document',
                'description' => 'Cualquier otro documento relacionado con el viaje',
                'icon' => 'heroicon-o-document',
                'color' => 'gray',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($defaultTypes as $type) {
            DB::table('attachment_types')->insert($type);
        }

        // Migrar datos existentes de attachment_type (enum) a attachment_type_id
        $existingAttachments = DB::table('travel_request_attachments')->get();

        foreach ($existingAttachments as $attachment) {
            $typeSlug = $attachment->attachment_type ?? 'other_document';

            $attachmentType = DB::table('attachment_types')
                ->where('slug', $typeSlug)
                ->first();

            if ($attachmentType) {
                DB::table('travel_request_attachments')
                    ->where('id', $attachment->id)
                    ->update(['attachment_type_id' => $attachmentType->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar datos del enum desde attachment_type_id
        $attachments = DB::table('travel_request_attachments')
            ->join('attachment_types', 'travel_request_attachments.attachment_type_id', '=', 'attachment_types.id')
            ->select('travel_request_attachments.id', 'attachment_types.slug')
            ->get();

        foreach ($attachments as $attachment) {
            DB::table('travel_request_attachments')
                ->where('id', $attachment->id)
                ->update(['attachment_type' => $attachment->slug]);
        }

        // Eliminar tipos por defecto
        DB::table('attachment_types')->whereIn('slug', [
            'hotel_reservation',
            'flight_ticket',
            'transport_receipt',
            'other_document',
        ])->delete();
    }
};
