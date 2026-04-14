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

1. Posiziona `index.php` nella root del document root (es. `/srv/http/index.php`)
2. Accedi a `http://localhost`

## Struttura

```php
$dirs = array_filter(scandir('/srv/http'), function($d) {
    return $d[0] !== '.' && is_dir("/srv/http/$d") && !in_array($d, ['.git', 'api']);
});
```

Modifica questo filtro per cambiare quali cartelle vengono mostrate.
