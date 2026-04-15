# localhost Index

⚠️ **Nota**: Se la navigazione nelle sottocartelle non funziona, vedi la sezione [URL Belle](#url-belle-opzionale) alla fine di questo file.

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

> **Nota:** Prima di eseguire lo script, assicurati che sia eseguibile:
> ```bash
> chmod +x install.sh
> ```

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

## Navigazione sottocartelle

Lo script supporta la navigazione nelle sottocartelle. Se una cartella contiene un `index.php` o `index.html`, viene caricato direttamente.

## URL Belle (opzionale)

Per avere URL come `/cartella/` invece di `/index.php?path=/cartella`, serve abilitare mod_rewrite.

### Se la navigazione non funziona:

1. Verifica che `mod_rewrite` sia abilitato:
   ```bash
   # Apache - Ubuntu/Debian
   a2enmod rewrite
   
   # Riavvia Apache
   sudo systemctl restart apache2
   ```

2. Abilita `AllowOverride` nel file di configurazione Apache (es. `/etc/apache2/sites-available/000-default.conf`):
   ```apache
   <Directory "/srv/http">
       AllowOverride FileInfo
   </Directory>
   ```

3. Riavvia Apache:
   ```bash
   sudo systemctl restart apache2
   ```

Lo script funziona anche senza mod_rewrite (usa URL con query string), ma le URL sono meno belle.

## Struttura

Lo script scansiona BASE_PATH e EXTRA_PATHS, escludendo le cartelle in EXCLUDED_DIRS.
