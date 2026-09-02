<?php

namespace Vendor\Package2\Jobs;

class __PACKAGE_UC__Job implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use \Illuminate\Foundation\Bus\Dispatchable, \Illuminate\Queue\InteractsWithQueue, \Illuminate\Queue\SerializesModels;

    public function __construct(public mixed $payload = [])
    {
    }

    public function handle(): void
    {
        //
    }
}
