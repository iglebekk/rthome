<?php

return [
    'create' => ['title' => 'Opprett en klubb', 'description' => 'Gi klubben et navn. Du blir dens første medlem.', 'submit' => 'Opprett klubb'],
    'settings' => [
        'title' => 'Klubbinnstillinger',
        'description' => 'Oppdater klubbnavnet eller fjern arbeidsområdet permanent.',
        'name' => 'Klubbnavn',
        'danger_title' => 'Slett denne klubben',
        'danger_description' => 'Dette sletter alle medlemmer, verv, arrangementer og opplastede arrangementsbilder permanent.',
        'confirm_label' => 'Skriv klubbnavnet for å bekrefte',
        'delete' => 'Slett klubben permanent',
        'events' => [
            'title' => 'Arrangementer',
            'description' => 'Importer arrangementer samlet med et JSON-objekt.',
            'import' => [
                'title' => 'Importer arrangementer',
                'description' => 'Lim inn et JSON-objekt som inneholder en events-tabell. Importerte arrangementer legges alltid til som nye; eksisterende arrangementer erstattes ikke.',
                'fields' => 'Hvert arrangement kan inneholde name, location, starts_at, ends_at, registration_url og short_description. Bilder støttes ikke.',
                'timezone' => 'Bruk ISO 8601-datoer med eksplisitt tidssoneforskyvning, for eksempel 2030-01-15T18:00:00+01:00. Datoer lagres i UTC.',
                'json_label' => 'Arrangementer som JSON',
                'agent_prompt_title' => 'Prompt for en AI-agent',
                'agent_prompt_trigger' => 'Hent AI-prompt',
                'agent_prompt_label' => 'Kopier denne fullstendige instruksjonen',
                'agent_prompt' => 'Opprett gyldig JSON for å importere arrangementer. Returner kun JSON uten Markdown. Bruk en events-tabell med name, location, starts_at, ends_at, registration_url og short_description. name, starts_at og ends_at er obligatoriske. ends_at må være senere enn starts_at. Bruk ISO 8601-datoer og maksimalt 100 arrangementer.',
                'close' => 'Lukk',
                'submit' => 'Importer arrangementer',
                'validation' => ['invalid_json' => 'Skriv inn et gyldig JSON-objekt som inneholder en events-tabell.', 'events_array' => 'Verdien i events må være en tabell.'],
                'messages' => ['imported' => '{1} Ett arrangement er importert.|[2,*] :count arrangementer er importert.'],
            ],
        ],
    ],
    'index' => ['title' => 'Klubbene dine', 'description' => 'Velg en klubb for å åpne oversikten.', 'empty_title' => 'Du har ingen klubber ennå', 'empty_description' => 'Opprett din første klubb for å komme i gang.'],
    'actions' => ['create' => 'Opprett klubb', 'create_another' => 'Opprett en klubb til'],
    'messages' => ['created' => 'Klubben er opprettet.', 'updated' => 'Klubbinnstillingene er oppdatert.', 'deleted' => 'Klubben er slettet.', 'deleted_with_last_member' => 'Det siste medlemmet og klubben er slettet.'],
];
