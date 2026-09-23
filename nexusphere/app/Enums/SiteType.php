<?php

namespace App\Enums;

enum SiteType: string
{
    case Template = 'template';
    case External = 'external';
}