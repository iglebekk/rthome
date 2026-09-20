<?php

namespace App\Enums;

enum InvoiceDocumentType: string
{
    case Invoice = 'invoice';
    case CreditNote = 'credit_note';
}
