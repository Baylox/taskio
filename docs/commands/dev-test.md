# dev:test

Lance les tests PHPUnit avec des options de filtrage et de couverture avancées.

## Usage

```bash
php bin/console dev:test [options]
```

## Options principales

| Option | Raccourci | Description |
|--------|-----------|-------------|
| `--type=TYPE` | `-t` | Filtre par type : `unit`, `functional`, `integration` |
| `--filter=PATTERN` | `-f` | Filtre par nom (regex) |
| `--group=GROUP` | `-g` | Filtre par groupe PHPUnit |
| `--coverage` | `-c` | Génère un rapport HTML de couverture |
| `--coverage-text` | - | Affiche la couverture dans la console |
| `--parallel` | `-p` | Exécution parallèle (nécessite paratest) |
| `--stop-on-failure` | `-s` | Arrête au premier échec |
| `--testdox` | - | Format de sortie lisible |

## Exemples

```bash
# Tous les tests
php bin/console dev:test

# Tests unitaires uniquement
php bin/console dev:test --type=unit

# Avec format lisible
php bin/console dev:test --testdox

# Filtrer par nom
php bin/console dev:test --filter=BoardVoter

# Avec couverture
php bin/console dev:test --coverage

# Stop au premier échec (debug)
php bin/console dev:test -s

# Combinaison
php bin/console dev:test -t unit --testdox -s
```

## Workflows courants

**Développement quotidien**
```bash
php bin/console dev:test -t unit --testdox
```

**Debug d'un test**
```bash
php bin/console dev:test --filter=MyTest -s -v
```

**Avant un commit**
```bash
php bin/console dev:test -t unit -s
```

**Avant un push**
```bash
php bin/console dev:test --coverage
```

**Exécution parallèle** (installer d'abord : `composer require --dev brianium/paratest`)
```bash
php bin/console dev:test --parallel
```

## Problèmes courants

**No tests executed**
```bash
# Vérifier le filtre ou le type
php bin/console dev:test -v
```

**Paratest not found**
```bash
composer require --dev brianium/paratest
```

**Coverage requires Xdebug/PCOV**
```bash
# Installer Xdebug ou PCOV
pecl install pcov
```

---

[← dev:reset](dev-reset.md) | [Commandes](README.md)
