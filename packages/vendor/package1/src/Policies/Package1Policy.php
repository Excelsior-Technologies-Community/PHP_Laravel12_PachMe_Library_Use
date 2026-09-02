<?php

namespace Vendor\Package1\Policies;

use Vendor\Package1\Models\__PACKAGE_UC__;
use Illuminate\Auth\Access\Response;

class __PACKAGE_UC__Policy
{
    public function viewAny(?User $user): Response
    {
        return $user ? Response::allow() : Response::deny();
    }

    public function view(?User $user, __PACKAGE_UC__ $model): Response
    {
        return $user ? Response::allow() : Response::deny();
    }

    public function create(?User $user): Response
    {
        return $user ? Response::allow() : Response::deny();
    }

    public function update(?User $user, __PACKAGE_UC__ $model): Response
    {
        return $user ? Response::allow() : Response::deny();
    }

    public function delete(?User $user, __PACKAGE_UC__ $model): Response
    {
        return $user ? Response::allow() : Response::deny();
    }
}
