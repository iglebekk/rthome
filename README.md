# RTHome

RTHome er en Laravel-applikasjon for drift av frivillige klubber og foreninger. Den samler medlemsregister, verv, arrangementer, delte lenker og fakturering i ett arbeidsrom per klubb.

README-en er skrevet for både utviklere og AI-agenter som trenger rask oversikt over domenet, arkitekturen og hvordan prosjektet kjøres lokalt.

## Hva applikasjonen gjør

En innlogget bruker jobber alltid innenfor en klubbkontekst. Fra klubbens arbeidsrom kan brukeren:

- administrere medlemmer og koble dem til brukerkontoer
- registrere verv og hvem som fyller dem
- opprette og importere arrangementer
- dele nyttige lenker internt i klubben
- invitere nye medlemmer via token-baserte invitasjoner
- opprette produkter og bruke dem som grunnlag for fakturering
- generere, sende, eksportere, kreditere og markere fakturaer som betalt
- dele offentlige arrangementssider via en egen offentlig token-lenke

Applikasjonen har også en egen aktiveringsflyt for medlemmer som inviteres inn før de har en brukerkonto.

## Viktige brukerflyter

### Innlogging og aktivering

- Gjester starter med å identifisere e-postadressen sin.
- Eksisterende brukere går videre til passordsteget.
- Nye brukere eller inviterte medlemmer kan fullføre registrering eller medlemsaktivering.
- Etter innlogging sendes brukeren videre til første tilgjengelige klubb, eller til opprettelse av klubb hvis brukeren ikke er medlem noe sted.

### Klubbadministrasjon

- Hver klubb har egne medlemmer, verv, arrangementer, lenker, produkter og fakturaer.
- Tilgang er konsekvent avgrenset til klubber brukeren faktisk tilhører.
- Dashboardet viser blant annet neste arrangement, fylte verv, lenker og medlemstall.

### Fakturering

- Produkter definerer pris, MVA-behandling og beskrivelse.
- En fakturakjøring velger mottakere og produktlinjer.
- Utstedte fakturaer kan ikke endres bortsett fra e-poststatus og betalingsstatus.
- Fakturaer kan eksporteres som nedlastbare batcher, og gamle eksportfiler ryddes via scheduler.

### Offentlige lenker

- `/join/{token}` brukes for klubbinvitasjoner.
- `/events/{token}` brukes for offentlige arrangementer.
- Token-baserte offentlige ruter er rate-begrenset.

## Domenemodell

| Entitet | Rolle |
| --- | --- |
| `Club` | Hovedaggregat for en klubb eller forening. Eier medlemmer, verv, arrangementer, lenker, produkter og fakturaer. |
| `Member` | En person i en klubb, med eventuell kobling til en `User`. Har også fakturainformasjon. |
| `Position` | Et verv i klubben, eventuelt knyttet til et medlem. |
| `Event` | Et arrangement med tidspunkt, sted og eventuell offentlig deling. |
| `Link` | En intern lenke som kan festes til dashboardet. |
| `ClubInvitation` | Invitasjon inn i en klubb via token. |
| `MemberActivation` | Aktiveringsflyt for inviterte medlemmer som må opprette eller fullføre konto. |
| `Product` | Fakturerbar vare eller tjeneste med pris og MVA-oppsett. |
| `InvoiceCreation` | Et utkast eller en utstedt fakturakjøring. |
| `Invoice` / `InvoiceLine` | Utstedte fakturaer og linjene deres. |
| `InvoiceExport` | Eksportbatch for nedlasting av flere fakturaer. |

## Teknisk oversikt

- **Backend:** Laravel 13, PHP, Eloquent, Fortify for autentisering
- **Frontend:** Blade, Flux UI-komponenter, Tailwind CSS v4, Vite
- **PDF og e-post:** `spatie/laravel-pdf`, `dompdf/dompdf`, Resend-støtte
- **Testing:** Pest, Laravel test helpers og browser tests
- **Standard lokal konfigurasjon:** SQLite, database-baserte sessions, cache og queue

## Viktige mapper

- `/home/runner/work/rthome/rthome/app/Http/Controllers` – webflyter og CRUD-endepunkter
- `/home/runner/work/rthome/rthome/app/Actions` – domenelogikk som import, invitasjoner, fakturering og synkronisering
- `/home/runner/work/rthome/rthome/app/Models` – sentrale domeneentiteter
- `/home/runner/work/rthome/rthome/resources/views` – Blade-visninger og UI-komponenter
- `/home/runner/work/rthome/rthome/routes/web.php` – alle brukerrettede ruter
- `/home/runner/work/rthome/rthome/tests/Feature` – funksjonell dekning av domene- og sideflyter
- `/home/runner/work/rthome/rthome/tests/Browser` – browser smoke tests

## Lokal utvikling

Prosjektet kjøres lokalt via Laravel Valet på `http://rthome.test`.

### Første oppsett

```bash
composer run setup
```

Skriptet installerer PHP- og Node-avhengigheter, oppretter `.env` ved behov, genererer appnøkkel, kjører migreringer og bygger frontend.

### Daglig arbeid

```bash
composer run dev
```

Dette starter utviklingsprosessene som prosjektet forventer, inkludert Vite, køarbeider, loggvisning og en intern dev-server. For manuell verifisering skal Valet-URL-en brukes, ikke `php artisan serve`.

### Nyttige kommandoer

```bash
php artisan test --compact
vendor/bin/pest tests/Feature --compact
npm run build
```

## Veiledning for AI-agenter

- Les prosjektreglene i `.ai/rules/` før du gjør endringer.
- Bruk `/home/runner/work/rthome/rthome/routes/web.php` og modellene i `/home/runner/work/rthome/rthome/app/Models` som kilde for domeneoversikt.
- Legg domenelogikk i eksisterende actions når endringer gjelder invitasjoner, import, fakturering eller synkronisering.
- Hold endringer klubbavgrenset; de fleste sider og handlinger forventer eksplisitt klubbtilhørighet.
- Bevar eksisterende arbeidsflyter for token-baserte offentlige sider og rate limiting.
- Bruk eksisterende tester som spesifikasjon før du endrer flyter.

## Relaterte filer

- `AGENTS.md` – agentinstrukser for dette repoet
- `CLAUDE.md` – Claude-spesifikke instruksjoner
- `GEMINI.md` – Gemini-spesifikke instruksjoner
- `docs/laravel-prinsipper.md` – generelle Laravel-prinsipper for prosjektet
