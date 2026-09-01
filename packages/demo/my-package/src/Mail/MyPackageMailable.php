<?php

namespace Demo\MyPackage\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class __PACKAGE_UC__Mailable extends Mailable
{
    use SerializesModels;

    public mixed $payload;

    public function __construct(mixed $payload = [])
    {
        $this->payload = $payload;
    }

    public function build()
    {
        return $this->subject('MyPackage Mail')
            ->view('my-package::emails.__package_uc__');
    }
}
