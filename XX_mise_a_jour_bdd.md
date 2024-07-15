# Comment revenir en arriere, si je fais une mise a jour de la base de donnes avec : php bin/console make:migration php bin/console doctrine:migrations:migrate" alors que c'etait une erreur?

Bien sûr, je peux vous expliquer comment annuler une migration dans Symfony si vous avez fait une mise à jour de la base de données par erreur. Voici les étapes à suivre :

1. Annuler la dernière migration :

Pour revenir en arrière sur la dernière migration exécutée, utilisez la commande suivante :

```bash
php bin/console doctrine:migrations:migrate prev
```

Cette commande annulera la dernière migration appliquée.

2. Vérifier l'état des migrations :

Après avoir annulé la migration, vérifiez l'état actuel des migrations avec :

```bash
php bin/console doctrine:migrations:status
```

Cela vous montrera quelle est la dernière migration appliquée.

3. Supprimer le fichier de migration erroné :

Si la migration était une erreur, vous pouvez supprimer le fichier de migration correspondant dans le dossier `migrations/`.

4. Mettre à jour la table de migration dans la base de données :

Pour s'assurer que la base de données est synchronisée avec les fichiers de migration, exécutez :

```bash
php bin/console doctrine:migrations:sync-metadata-storage
```

5. Vérifier à nouveau l'état des migrations :

Exécutez une nouvelle fois :

```bash
php bin/console doctrine:migrations:status
```

pour vous assurer que tout est en ordre.

6. Si nécessaire, revenir à une version spécifique :

Si vous devez revenir à une version antérieure spécifique, vous pouvez utiliser :

```bash
php bin/console doctrine:migrations:migrate 'DoctrineMigrations\Version20YYMMDDHHMMSS'
```

Remplacez 'Version20YYMMDDHHMMSS' par le nom de la version à laquelle vous souhaitez revenir.

7. Nettoyage de la base de données (optionnel) :

Si la migration a créé des tables ou des colonnes que vous ne voulez pas, vous devrez peut-être les supprimer manuellement via SQL.

8. Régénérer une nouvelle migration si nécessaire :

Si vous avez besoin de créer une nouvelle migration corrigée, vous pouvez utiliser :

```bash
php bin/console make:migration
```

Assurez-vous de bien vérifier le contenu de cette nouvelle migration avant de l'appliquer.

Important :
- Faites toujours une sauvegarde de votre base de données avant d'effectuer des opérations de migration.
- Soyez très prudent lors de l'annulation des migrations, surtout en production, car cela peut entraîner une perte de données.
- Si vous travaillez en équipe, assurez-vous de communiquer ces changements à vos collègues.

En suivant ces étapes, vous devriez pouvoir revenir en arrière sur une migration erronée et remettre votre base de données dans l'état souhaité.