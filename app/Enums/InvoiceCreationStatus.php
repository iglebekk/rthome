<?php

namespace App\Enums;

enum InvoiceCreationStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
}
