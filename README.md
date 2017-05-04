[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b32f2cd1-f941-4327-b1b5-21960d5bbebe/small.png)](https://insight.sensiolabs.com/projects/b32f2cd1-f941-4327-b1b5-21960d5bbebe) [![Latest Stable Version](https://poser.pugx.org/rma/rmabdd_light/v/stable)](https://packagist.org/packages/rma/rmabdd_light) [![Latest Unstable Version](https://poser.pugx.org/rma/rmabdd_light/v/unstable)](https://packagist.org/packages/rma/rmabdd_light)

## Si vous souhaitez téléchargé rmaBDD comme Bundle, vous avez 2 possibilités :

> Utiliser Composer:
```
    $ composer require rma/rmabdd_light:~{last_version_stable}*
```

Via le fichier json :
```
    "require": {
        "rma/rmabdd_light":"~{last_version_stable}*"
    }
```

Activer dans votre app/AppKernel.php le bundle : 

```
    new RMA\Bundle\DumpBundle\RMADumpBundle()
```

Ensuite, vous pouvez lancer composer update afin configurer votre bundle.

## Dépendances
    "php": ">=5.6",
    "symfony/symfony": ">=2.8.6|<3.0",
    "doctrine/orm": "^2.4.8",
    "doctrine/doctrine-bundle": "~1.4",
    "symfony/monolog-bundle": "~2.4",
    "sensio/distribution-bundle": ">=3.0|<=5.0.5",
    "incenteev/composer-parameter-handler": "~2.0",
    "ifsnop/mysqldump-php" : "~2.1"

## Les commandes :

Pour voir les commandes mises à votre disposition rendez-vous à la racine et écrivez :
    
> php app/console 

*Les commandes mises à disposition sont préfixées par "rma:"*
    
    > rma:dump:help ---- Permet d'obtenir des informations complémentaires pour l'utilisation du plugin

    > rma:dump:inspect ---- (alias [inspect]) Vous aide pour définir les configuration de vos connexions base de données. Liste vos connexions chargées et les paramètres liés

    > rma:dump:database ---- (alias [dump]) Permet de réaliser un dump 
        Options :
            --one pour sauvegarder une base unique
            --i pour ouvrir l'interface d'intéractions pour les données de connexion (sinon les infos en parameters seront prises par défaut)
            --ftp permet de sauvegarder le dump en FTP. Ne fonctionne actuellement que pour une archive zippée. 
            --repertoire_name permet de définir un nom custom pour le dump
            --all permet de dump toutes les bases disponibles avec les parameters fournis (annule l'option -one et les bases de données en argument)
            
        Arguments :
        Les bases de données à extraire. Si aucun argument, toutes les bases seront sauvegardées
            Exemple avec argument : 
                php app/console rma:dump:database rmabdd mysql --name=dump_before_mep
            Exemple sans argument : 
                php app/console rma:dump:database

    > rma:dump:cron ---- Commande prévue spécialement pour les CRON
        Permet de réaliser un dump en crontab. Si vous ne mettez pas d'argument toutes les bases de données seront sauvegardées.
        Par défaut les paramètres sont ceux définis au niveau de votre paramters.yml
        Options : 
            --host
            --port
            --user
            --password  (pour mettre un mot de passe vide, mettez le paramètre --password=none)
            --compress  {none, gzip, bzip2}
            --zip       {yes, no}
            --dir_zip   Pour les dir, vous devez doubler les DIRECTORY SEPARATOR 
            --dir_dump  exemple :  --dir_dump=C:\\Users\\rmA\\Desktop
            --ftp_ip 
            --ftp_username
            --ftp_password
            --ftp_port
            --ftp_timeout
            --ftp_path
            --all

            Exemple :
                php app/console rma:dump:cron --host=127.0.0.1 --password=none --username=root --dir_zip=C:\\Users\\rmA\\Desktop\\Save

        Arguments :
        Les bases de données à extraire. Si aucun argument, toutes les bases seront sauvegardées
            Exemple avec argument : 
                php app/console rma:dump:cron rmabdd mysql --host=127.0.0.1
            Exemple sans argument : 
                php app/console rma:dump:cron 


    > rma:dump:clean ---- Commande prévue pour nettoyer les répertoires de dump
        Permet de supprimer des dumps
        Par défaut le répertoire à vider est celui défini au niveau du parameters.yml
        Options : 
            --nb_jour ; Permet de définir en nombre de jours, la date à partir de laquelle les dump seront conservés
            --nombre_dump ; Permet de définir le nombre de dump à conserver

            Exemple :
                php app/console rma:dump:clean --nb-jour=4 
            Tous les dates de plus de 4 jours seront supprimés
                php app/console rma:dump:clean --nombre=15
            Va conserver les 15 derniers dumps 

    > rma:dump:sync ---- Commande pour synchroniser les logs de dump avec les dumps effectivement présents dans le répertoire de dump
        Permet notamment de mettre à jour le dossier des logs dans le cas où vous supprimeriez manuellement des dumps
        Par défaut le répertoire à vider est celui défini au niveau du parameters.yml
        Options :
            --dir_dump ; permet de définir un répertoire à gérer spécifique 


    > rma:dump:export ---- (alias [export]) Permet de réaliser un export d'une base de données 
        Options :
            --script ; le nom du fichier stocké dans le répertoire web/script pour (exemple test.sql)
            --repertoire_name ; pour définir un nom custom au répertoire d'export
            --keep_tmp ; laisse la basededonnées temporaire créée pour la migration sur le serveur database
            --name_database_temp ; permet de donner un nom custom à la database créée pour l'export (ce nom ne doit pas être porté par une database déjà existante sur le serveur)
            --ftp  ; permet d'envoyer l'export en FTP selon les paramètres définis dans parameters.yml pour rma_ftp

    > rma:restaure:database ---- (alias [restaure]) Permet de restaurer une base à partir d'un script SQL
        Options : 
            --new_database_name ; le nom à donner à la base de données restaurée - Obligatoire
            --script_sql ; le nom du fichier (.sql) à importer - Optionnel

## Définition des configurations 

Par défaut, *les paramètres définis pour doctrine* seront pris pour effectuer les dumps.

*Nouveauté 0.5* : 
        ** Vous pouvez désormais définir plusieurs configurations de connexion.
        ** Vous pouvez désormais définir plusieurs configurations FTP.

Si vous souhaitez modifier les configurations, vous avez accès aux paramètres suivants à mettre directement dans vos parameters.
    
Pour vous aider dans la définition de vos configurations, vous avez accès à des commandes pour vous récapituler les configurations chargées à partir des informations saisies

> php app/console inspectConnexions

> php app/console inspectFtps

Retourne un tableau avec les différentes connexions chargées et les paramètres correspondants

### Configuration dump et export : 
 _ Nouveautés v0.6 _  Vous pouvez définir en index de vos connexions le nom de votre connexion
 ATTENTION ! Les clés de parameters ont changées ! Il n'est plus nécessaire de prefixer les keys
    - rma_connexions:
        localhost: 
           driver: pdo_mysql
           host: 192.154.125.154
           port: 3306, 
           user: ronan-user 
           password: passwordInconnu 
        Integration: 
            driver: pdo_mysql
            user: testt
            password: toto
            port: 33009
            host: 192.168.104.2     
    - rma_nb_jour:                  5
    - rma_nombre_dump:              10
    - rma_dir_dump:                 %kernel.root_dir%/../../rmabundle/dump
    - rma_keep_tmp:                 no ; {yes|no} 
    - rma_script:                   script_migration.sql 
    - rma_dir_script_migration:     %kernel.root_dir%/../../rmabundle/script

ATTENTION : Si vous souhaitez utiliser votre rma_name_connexion lors de l'execution d'une commandre cron, vous ne devez pas mettre de caractères spéciaux (%, espace, ^)

#### Ajout de paramètres aux paramètres Doctrine : 

Si vous souhaitez vous servir des paramètres définis pour Doctrine afin de faire vos dump / export.
Vous pouvez définir votre connexion à partir des informations suivantes : 

    - database_driver: pdo_mysql

    - database_host: localhost

    - database_port: 3306

    - database_user: root

    - database_password:

    - rma_excludes: [mysql, databaseToExclude] 


### Configuration FTP des dump 
    - rma_ftp:  no ; {yes|no} 
    - rma_ftps:
        - { rma_name_ftp: Serveur 1, rma_ftp_ip: 127.0.0.1, rma_ftp_username: rma, rma_ftp_password: password, rma_ftp_port: 21, rma_ftp_timeout: 120}
        - { rma_name_ftp: Intégration, rma_ftp_ip: 127.0.0.12, rma_ftp_username: root, rma_ftp_password: secret, rma_ftp_path: /home/root/dump}     

### Configuration compression des dump 
    - rma_compress:                 gzip ; {none|gzip}
    - rma_zip:                      no ; {yes|no} 
    - rma_dir_zip:                  %kernel.root_dir%/../web/zip

*Attention* : Pour mettre un password vide, n'oubliez pas le 'none'.

*Attention* : Pour ne pas effacer de dump au fur et à mesure renseigner 'none' aux champs nb_jour et nombre_dump
