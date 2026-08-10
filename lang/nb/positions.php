<?php

return [
    'title' => 'Verv',
    'description' => 'Prioriter klubbansvar og gjør ledige roller synlige.',
    'create_title' => 'Opprett verv',
    'create_description' => 'Definer et verv med valgfritt medlem, prioritert rekkefølge og periode.',
    'edit_title' => 'Rediger verv',
    'edit_description' => 'Oppdater ansvar, valgfritt medlem, rekkefølge eller periode.',
    'fields' => [
        'name' => 'Navn på verv',
        'member' => 'Medlem (valgfritt)',
        'sort_order' => 'Prioritert rekkefølge (valgfritt)',
        'start_date' => 'Startdato',
        'end_date' => 'Sluttdato',
    ],
    'select_member' => 'Ingen medlem tildelt',
    'unfilled' => 'Ledig',
    'period' => [
        'open' => 'Åpent',
    ],
    'empty' => 'Ingen verv ennå',
    'empty_description' => 'Opprett verv for å tydeliggjøre hvordan klubben er organisert.',
    'actions' => [
        'create' => 'Opprett verv',
        'edit' => 'Rediger verv',
        'delete' => 'Slett verv',
    ],
    'delete_title' => 'Slett :name?',
    'delete_description' => 'Dette fjerner vervet permanent.',
    'messages' => [
        'created' => 'Vervet er opprettet.',
        'updated' => 'Vervet er oppdatert.',
        'deleted' => 'Vervet er slettet.',
    ],
];
