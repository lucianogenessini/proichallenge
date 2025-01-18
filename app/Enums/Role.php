<?php

namespace App\Enums;

enum Role:string
{
    case SUPER_ADMIN_ROLE = 'super-admin';
    case OPERATION_ROLE = 'operation';
}
