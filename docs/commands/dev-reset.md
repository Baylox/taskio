# dev:reset

Réinitialise l'environnement de développement (cache, base de données, migrations, fixtures).

⚠️ **ATTENTION** : Supprime toutes les données de la base de données !

## Usage

```bash
php bin/console dev:reset
```

## Ce que ça fait

1. Vide le cache
2. Supprime la base de données
3. Recrée la base de données
4. Exécute les migrations
5. Charge les fixtures

## Exemples

```bash
# Reset standard (avec confirmation)
php bin/console dev:reset

# Après avoir changé de branche
git checkout feature/new-schema
php bin/console dev:reset

# Avant de tester les migrations
php bin/console dev:reset && php bin/console dev:check
```

## Quand l'utiliser

- Changement de branche avec nouvelles migrations
- Base de données "corrompue" ou incohérente
- Test des migrations depuis zéro
- Besoin d'un environnement propre

## Problèmes courants

**Migration failed**
```bash
# Vérifier les fichiers de migration
# Ou exécuter manuellement :
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

**Fixtures failed**
```bash
# Vérifier les fixtures ou les ignorer
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
# (skip fixtures)
```

