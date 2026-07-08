# ilbronza/temporarylinks

Link temporanei controllati per operatori esterni: URL pubblici `https://dominio.it/t/{token}` con scadenza, revoca manuale, limite visite, password/PIN opzionale, log accessi e protezione della pagina di destinazione.

Documentazione completa del flusso: [FUNZIONAMENTO.md](FUNZIONAMENTO.md).

## Installazione nel progetto host

Aggiungere il path repository (se non già coperto dal wildcard) e la dependency:

```json
"repositories": [
    { "type": "path", "url": "../../../pacchetti/TemporaryLinks" }
],
"require": {
    "ilbronza/temporarylinks": "*"
}
```

Poi:

```bash
composer update ilbronza/temporarylinks
php artisan migrate
```

Config pubblicabile con `php artisan vendor:publish --tag=temporarylinks.config`.

## Creazione programmatica (modo preferito)

```php
use IlBronza\TemporaryLinks\Facades\TemporaryLinks;

$result = TemporaryLinks::create()
    ->name('Compilazione rapportino')
    ->route('operators.timesheet.edit', ['operator' => $operator->getKey()])
    ->for($operator)                 // subject morph, opzionale
    ->expiresInMinutes(1440)         // default da config
    ->maxVisits(3)                   // opzionale
    ->password('1234')               // opzionale
    ->consumeOnFirstSuccess()        // opzionale, link monouso
    ->save();

$result->getPublicUrl(); // UNICO momento in cui l'URL col token in chiaro è disponibile
$result->link;           // il model TemporaryLink
```

Il token non viene mai salvato in chiaro: in tabella c'è solo `sha256`. Se l'URL va perso, usare l'azione admin "Genera nuovo URL" o `$link->regenerateToken()`.

## Proteggere la destinazione (obbligatorio)

Il redirect da solo non protegge la pagina di destinazione. Applicare il middleware alle route raggiunte dai link:

```php
Route::get('timesheet/{operator}/edit', ...)
    ->middleware('temporarylink.verified')
    ->name('operators.timesheet.edit');
```

Il middleware verifica che la sessione arrivi da un link temporaneo valido (flag messo al redirect), ricontrolla revoca/scadenza e rende disponibile il link corrente in `$request->attributes->get('temporaryLink')` (da cui leggere `subject`).

## Flusso pubblico

`GET /t/{token}` → verifica token (hash) → gate (revocato / consumato / non iniziato / scaduto / limite visite / destinazione valida) → eventuale challenge password → eventuale pagina interstiziale "Prosegui" (solo per link monouso o con `max_visits`, per evitare che gli email scanner brucino il link) → increment visite atomico → log accesso → grant sessione → redirect.

Throttle attivo sia sul GET pubblico sia sul POST password (configurabile).

## Admin CRUD

Route sotto `temporary-links-management` con middleware da `config('temporarylinks.middleware.admin')`. Azioni extra oltre al resource: `revoke`, `reactivate`, `extend`, `regenerateToken`, `duplicate`, `preview` (apre la destinazione senza contare la visita). Dopo creazione/rigenerazione l'URL in chiaro viene flashato in sessione (`temporarylinks.plainUrl` e `message`) e mostrato una sola volta.

## Comandi

- `php artisan temporarylinks:cleanup` — elimina access log più vecchi di `cleanup.access_log_retention_days`
- `php artisan temporarylinks:report` — conteggi per stato

## Eventi

`TemporaryLinkCreated`, `TemporaryLinkOpened`, `TemporaryLinkBlocked`, `TemporaryLinkRevoked`, `TemporaryLinkConsumed`.

## Override

Come gli altri package IlBronza, tutto passa da `config/temporarylinks.php`: classi model, tabelle, controllers (`crud`, `actions`, `redirect`), parametersFiles, middleware, throttle, `allow_absolute_urls` + `allowed_hosts` per destinazioni URL assolute (default disabilitate, anti open-redirect).

## Stati (solo calcolati)

`draft`, `scheduled`, `active`, `expired`, `revoked`, `consumed`, `limit_reached` — accessor `$link->status` / `$link->translated_status`, scopes `active()`, `scheduled()`, `expired()`, `revoked()`, `consumed()`, `neverOpened()`.
