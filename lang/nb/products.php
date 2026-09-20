<?php

return [
    'title' => 'Produkter',
    'description' => 'Administrer produkter og priser for fakturaer.',
    'create_title' => 'Opprett produkt',
    'create_description' => 'Legg til et gjenbrukbart produkt for nye fakturaer.',
    'edit_title' => 'Rediger produkt',
    'edit_description' => 'Oppdater produktet. Eksisterende fakturalinjer endres ikke.',
    'empty' => 'Ingen produkter ennå',
    'empty_description' => 'Opprett det første produktet for å lage fakturaer.',
    'fields' => [
        'name' => 'Produktnavn',
        'description' => 'Beskrivelse',
        'price' => 'Pris inklusive MVA',
        'vat_rate' => 'MVA-behandling',
        'active' => 'Tilgjengelig for nye fakturaer',
    ],
    'vat_rates' => ['25' => '25 % MVA', '15' => '15 % MVA', '12' => '12 % MVA', 'exempt' => 'MVA-fritatt', 'outside_scope' => 'Utenfor MVA-området'],
    'actions' => ['create' => 'Opprett produkt', 'edit' => 'Rediger', 'delete' => 'Slett'],
    'delete_title' => 'Slett :name?',
    'delete_description' => 'Produktet blir utilgjengelig for nye fakturaer. Eksisterende fakturaer endres ikke.',
    'used_by_draft' => 'Brukes i et utkast',
    'messages' => ['created' => 'Produkt opprettet.', 'updated' => 'Produkt oppdatert.', 'deleted' => 'Produkt slettet.'],
];
