<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddInvoice extends Notification
{
    use Queueable;
    private $invoice_id;

    public function __construct($invoice_id)
    {
        $this->invoice_id = $invoice_id;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $id = $this->invoice_id;
        return (new MailMessage)
                    ->subject('فاتورة جديدة')
                    ->line('إضافة فاتورة جديدة')
                    ->action('عرض الفاتورة', url('/invoices/details/'.$id))
                    ->line('شكرا لإستخدامك تطبيق إدارة الفواتير');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toDatabase($notifiable)
    {
        $id = $this->invoice_id;
        return [
            'id'=> $id,
            'title'=>'تم اضافة فاتورة جديد بواسطة :',
            'user'=> auth()->user()->name,
        ];
    }
}
