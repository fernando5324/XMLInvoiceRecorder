<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VouchersCreatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public array $vouchers;
    public array $messages;
    public User $user;

    public function __construct(array $vouchers, User $user, array $messages)
    {
        $this->vouchers = $vouchers;
        $this->user = $user;
        $this->messages = $messages;

    }

    public function build(): self
    {
        return $this->view('emails.comprobante')
            ->with(['comprobantes' => $this->vouchers, 'user' => $this->user, "messages" => $this->messages]);
    }
}
