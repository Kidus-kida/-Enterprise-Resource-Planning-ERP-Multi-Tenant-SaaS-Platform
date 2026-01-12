<?php

namespace App\Enums;

enum UserType: string
{
    case SUPERADMIN = 'Super Admin';
    case EMPLOYEE = 'Employee';
    case ADMIN = 'admin';
    case CLIENT = 'Client';
}
