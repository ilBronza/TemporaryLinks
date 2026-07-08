# Funzionamento di TemporaryLinks

`ilbronza/temporarylinks` gestisce link pubblici temporanei verso route o URL controllati. Il caso d'uso principale e' dare a un utente esterno un URL del tipo `/t/{token}` che rimane valido solo entro certe condizioni: scadenza, revoca, numero massimo di visite, password opzionale e consumo al primo accesso riuscito.

Il package non sostituisce l'autorizzazione della pagina finale: il redirect pubblico concede una sessione temporanea, poi la route di destinazione deve essere protetta con il middleware `temporarylink.verified`.

## Componenti principali

Il package e' registrato dal service provider `IlBronza\TemporaryLinks\TemporaryLinksServiceProvider`.

In fase di boot il provider:

- carica config, route, migrazioni, viste e traduzioni;
- registra il middleware `temporarylink.verified`;
- registra i comandi Artisan quando l'app gira da console;
- registra la morph map per i model del package.

La configurazione vive in `config/temporarylinks.php` e governa:

- prefisso route admin e pubblico;
- middleware admin e pubblico;
- throttle pubblico e password;
- durata default dei link;
- lunghezza token;
- abilitazione di URL assoluti;
- host ammessi;
- classi model, controller e parameters file.

## Creazione di un link

Il modo preferito e' usare la facade `TemporaryLinks`:

```php
use IlBronza\TemporaryLinks\Facades\TemporaryLinks;

$result = TemporaryLinks::create()
	->name('Compilazione rapportino')
	->route('operators.timesheet.edit', ['operator' => $operator->getKey()])
	->for($operator)
	->expiresInMinutes(1440)
	->maxVisits(3)
	->password('1234')
	->consumeOnFirstSuccess()
	->save();

$url = $result->getPublicUrl();
$link = $result->link;
```

La creazione passa da `TemporaryLinkBuilder`.

Il builder prepara un model `TemporaryLink`, imposta una scadenza default da config e permette di indicare:

- nome e descrizione;
- destinazione come route Laravel;
- destinazione come URL assoluto, se abilitato da config;
- subject collegato via morph;
- data di inizio validita';
- data di scadenza;
- numero massimo di visite;
- password/PIN;
- consumo al primo accesso riuscito.

Quando viene salvato, il model genera un token casuale. In database non viene salvato il token in chiaro: viene salvato solo `sha256(token)` nel campo `token_hash`.

Il token in chiaro e' disponibile solo nel `CreatedLinkResult` oppure, nel CRUD admin, flashato in sessione subito dopo creazione, duplicazione o rigenerazione. Se l'URL viene perso, bisogna rigenerare il token.

## Destinazioni

Un link puo' puntare a due tipi di destinazione.

`route`
: usa il nome di una route Laravel e un array di parametri. La destinazione e' valida solo se `Route::has($destination_route)` ritorna vero.

`url`
: usa un URL assoluto. Per default e' disabilitato con `allow_absolute_urls = false`, per evitare open redirect. Se abilitato, sono ammessi solo schemi `http` e `https`, senza username/password nell'URL. Se `allowed_hosts` e' valorizzato, l'host deve essere in quella lista.

La risoluzione passa da `DestinationHelper`.

## Apertura pubblica

Le route pubbliche sono sotto il prefisso configurato in `temporarylinks.publicPrefix`, di default `/t`.

Il flusso principale e':

1. `GET /t/{token}` arriva a `TemporaryLinkRedirectController@show`.
2. Il token in chiaro viene hashato e cercato nel campo `token_hash`.
3. Se non esiste un link, viene registrato un accesso bloccato con motivo `not_found` e viene mostrata la vista errore.
4. Se il link esiste, `OpeningGateHelper` verifica lo stato.
5. Se il link richiede password e la sessione non e' gia' verificata, viene mostrata la vista password.
6. Se il link richiede interstitial e la sessione non ha ancora confermato, viene mostrata la pagina "prosegui".
7. Se tutti i controlli passano, il controller risolve la destinazione, incrementa atomicamente `visits_count`, registra il grant in sessione, logga l'accesso e fa redirect.

Il gate blocca il link quando:

- e' revocato;
- e' gia' consumato;
- non e' ancora iniziato;
- e' scaduto;
- ha raggiunto il limite visite;
- la destinazione non e' valida.

Gli accessi vengono salvati in `TemporaryLinkAccess`, con risultato `allowed` o `blocked` e con eventuale motivo di blocco.

## Password e interstitial

Se il link ha `password_hash`, l'utente deve inviare la password alla route `POST /t/{token}/password`. Dopo una password corretta, `SessionHelper` registra in sessione che quel link e' stato verificato.

L'interstitial viene richiesto quando:

- `consume_on_first_success` e' attivo;
- oppure `max_visits` non e' nullo.

Serve a evitare che scanner automatici di email o chat consumino il link senza un'azione esplicita dell'utente. Dopo il POST di conferma, la sessione viene marcata come confermata e il browser torna al `GET /t/{token}`.

## Visite, consumo e concorrenza

L'incremento visite avviene in `TemporaryLink::markVisited()`.

Se il link ha un limite visite, l'update applica anche la condizione `visits_count < max_visits`. Questo rende il controllo resistente a richieste concorrenti: se due richieste arrivano insieme quando rimane una sola visita disponibile, una sola riesce a incrementare.

Se `consume_on_first_success` e' attivo, dopo l'incremento riuscito il link viene marcato come consumato con `consumed_at`.

## Protezione della route finale

Il redirect pubblico non basta a proteggere la destinazione. La route finale deve usare il middleware:

```php
Route::get('timesheet/{operator}/edit', ...)
	->middleware('temporarylink.verified')
	->name('operators.timesheet.edit');
```

Il middleware `TemporaryLinkVerifiedMiddleware` legge dalla sessione le destinazioni concesse, confronta l'URL richiesto con quello concesso e cerca il link corrispondente.

Se trova un link valido:

- lo mette in `$request->attributes->get('temporaryLink')`;
- lascia passare la request.

Se non trova un link valido, oppure se il link e' stato revocato o e' scaduto, interrompe con `403`.

Questo significa che la pagina finale puo' recuperare il link corrente e, se serve, il subject associato:

```php
$temporaryLink = request()->attributes->get('temporaryLink');
$subject = $temporaryLink?->subject;
```

## CRUD amministrativo

Le route admin sono sotto `temporary-links-management` e usano il middleware configurato in `temporarylinks.middleware.admin`.

Il controller CRUD principale e' `CrudTemporaryLinkController`. Oltre alle normali azioni CRUD, sono disponibili azioni dedicate in `TemporaryLinkActionsController`:

- `revoke`: revoca manualmente il link;
- `reactivate`: rimuove la revoca;
- `extend`: aggiorna la scadenza;
- `regenerateToken`: genera un nuovo token e mostra il nuovo URL una sola volta;
- `duplicate`: duplica il link, azzera lo stato operativo e genera un nuovo token;
- `preview`: apre la destinazione senza incrementare le visite.

I campi del form CRUD sono definiti da `TemporaryLinkParameters`.

## Stati

Gli stati sono calcolati, non salvati in un campo dedicato.

L'ordine di priorita' e':

1. `draft`, se manca `destination_type`;
2. `revoked`, se `revoked_at` e' valorizzato;
3. `consumed`, se `consumed_at` e' valorizzato;
4. `limit_reached`, se `visits_count >= max_visits`;
5. `scheduled`, se `starts_at` e' nel futuro;
6. `expired`, se `expires_at` e' nel passato;
7. `active`, in tutti gli altri casi.

Il model espone anche gli accessor `status` e `translated_status`, piu' alcuni scope: `active`, `scheduled`, `expired`, `revoked`, `consumed`, `neverOpened`.

## Database

Le migrazioni creano due tabelle.

`temporarylinks__temporary_links`
: contiene il link, il token hashato, la destinazione, le date di validita', revoca, consumo, limiti visita, password hashata, subject morph e utente creatore.

`temporarylinks__accesses`
: contiene il log degli accessi, con link associato, data, IP, user agent, risultato, motivo di blocco e destinazione raggiunta.

Il package usa UUID per `TemporaryLink` e ID incrementale per `TemporaryLinkAccess`.

## Eventi

Il package emette eventi nei punti principali del ciclo di vita:

- `TemporaryLinkCreated`;
- `TemporaryLinkOpened`;
- `TemporaryLinkBlocked`;
- `TemporaryLinkRevoked`;
- `TemporaryLinkConsumed`.

Servono per notifiche, audit aggiuntivo o integrazioni nel progetto host.

## Comandi Artisan

Sono disponibili due comandi:

```bash
php artisan temporarylinks:cleanup
php artisan temporarylinks:report
```

`temporarylinks:cleanup` elimina i log accesso piu' vecchi del numero di giorni configurato in `temporarylinks.cleanup.access_log_retention_days` e mostra quanti link scaduti sono ancora presenti.

`temporarylinks:report` mostra un riepilogo dei link per stato: attivi, programmati, scaduti, revocati, consumati, mai aperti e totale.

## Punti di estensione

Il package e' pensato per essere esteso soprattutto via config.

Si possono sostituire:

- model `TemporaryLink` e `TemporaryLinkAccess`;
- controller CRUD, actions e redirect;
- parameters file del CRUD;
- middleware admin/pubblico;
- throttle;
- prefissi route;
- policy di destinazione URL assolute.

Per mantenere il comportamento coerente, il progetto host dovrebbe preferire queste estensioni via config invece di modificare direttamente il package.
