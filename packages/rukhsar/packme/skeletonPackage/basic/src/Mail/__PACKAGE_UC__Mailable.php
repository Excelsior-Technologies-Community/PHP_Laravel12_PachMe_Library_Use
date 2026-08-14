<?php

namespace :VendorName\:PackageName\Mail;

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
        return $this->subject(':PackageName Mail')
            ->view(':package_name::emails.__package_uc__');
    }
}
