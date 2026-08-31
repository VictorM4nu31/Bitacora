<?php

namespace App\Notifications;

use App\Models\MaintenanceSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceDue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MaintenanceSchedule $schedule) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $equipmentName = $this->schedule->equipment->name ?? 'Equipo';

        return (new MailMessage)
            ->subject('Mantenimiento programado: '.$equipmentName)
            ->line('Se acerca un mantenimiento programado para el siguiente equipo:')
            ->line($equipmentName)
            ->line('Fecha programada: '.$this->schedule->next_due_at->toDateTimeString())
            ->action('Ver equipo', url('/equipment/'.$this->schedule->equipment_id))
            ->line('Bitácora Inteligente para Técnicos');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'maintenance_schedule_id' => $this->schedule->id,
            'equipment_id' => $this->schedule->equipment_id,
            'equipment_name' => $this->schedule->equipment->name,
            'next_due_at' => $this->schedule->next_due_at->toDateTimeString(),
        ];
    }
}
