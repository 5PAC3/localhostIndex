# localhost Index

Launcher PHP per navigare i progetti nella directory `/srv/http` dello stack LAMP.

## Funzionamento

Scansiona automaticamente tutte le sottocartelle di `/srv/http` (escludendo `.git` e `api`) e le elenca come link cliccabili.

## Caratteristiche

- **Navigazione da tastiera**: `↑`/`↓` o `j`/`k` per muoversi, `Enter` per aprire, `/` per cercare, `Esc` per chiudere la ricerca
- **Pinning**: `p` per fissare un sito in cima alla lista (perso in `localStorage`)
- **Ricerca**: filtro istantaneo per nome o URL
- **Design**: tema scuro con JetBrains Mono, responsive per mobile
- *Nessuna cache*: header `no-store` per evitare caching

## Setup

### Opzione 1: Script di installazione (consigliato)

Lo script `install.sh` crea automaticamente i symlink necessari:

```bash
./install.sh
```

Se la tua document root è diversa da `/srv/http`:

```bash
HTTP_ROOT=/var/www/html ./install.sh
```

### Opzione 2: Manuale

1. Crea il symlink del file index.php:
   ```bash
   ln -s /path/to/localhostIndex/index.php /srv/http/index.php
   ```
2. (Opzionale) Crea symlink per le app di sistema:
   ```bash
   ln -s /usr/share/webapps/phpMyAdmin /srv/http/phpmyadmin
   ```
3. Accedi a `http://localhost`

## Configurazione

```php
define('BASE_PATH', '/srv/http');       // directory principale da scansionare
define('EXCLUDED_DIRS', ['.git', 'api']); // cartelle da escludere
define('EXTRA_PATHS', ['/usr/share/webapps']); // directory extra (es. app di sistema)
```

Modifica questi valori per cambiare quali cartelle vengono mostrate.

## Struttura

Lo script scansiona BASE_PATH e EXTRA_PATHS, escludendo le cartelle in EXCLUDED_DIRS.
