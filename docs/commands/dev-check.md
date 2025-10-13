# dev:check

Vérifie l'état de l'environnement de développement (base de données, cache, dépendances, permissions).

## Usage

```bash
php bin/console dev:check
```

## Ce qui est vérifié

- **Database** : Connexion, tables, migrations
- **Cache** : Répertoire, taille, permissions
- **Environment** : Mode, debug, PHP version, memory limit
- **Dependencies** : composer.lock, vendor/, node_modules/
- **Permissions** : var/cache, var/log

## Exemples

```bash
# Vérification standard
php bin/console dev:check

# Après avoir pull des changements
git pull && composer install && php bin/console dev:check

# Dans un pipeline CI
php bin/console dev:check && php bin/console dev:test
```

## Problèmes courants

**Database connection failed**
```bash
# Vérifier DATABASE_URL dans .env.local
# Relancer le serveur de base de données
```

**No tables found**
```bash
php bin/console doctrine:migrations:migrate
```

**Cache not writable**
```bash
chmod -R 777 var/cache var/log  # Linux/Mac
```

**Missing dependencies**
```bash
composer install
npm install
```

---

[← Commandes](README.md) | [dev:reset →](dev-reset.md)
