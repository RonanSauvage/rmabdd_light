rmABDD_light :

Ce projet est une adaptation light de rmaBDD. Elle n'intègre que la partie console.

### Si vous souhaitez téléchargé rmaBDD comme Bundle, vous avez 2 possibilités :

Utiliser Composer:
    $ composer require rma/rmabdd_light:~0.3.*

Via le fichier json :
    "require": {
        "rma/rmabdd_light":"~0.3.*"
    }

Ensuite, vous pouvez lancer composer update afin configurer votre bundle.

### Dépendances
    "php": ">=5.3.9",
    "symfony/symfony": "2.8.*",
    "doctrine/orm": "^2.4.8",
    "doctrine/doctrine-bundle": "~1.4",
    "symfony/swiftmailer-bundle": "~2.3",
    "symfony/monolog-bundle": "~2.4",
    "sensio/distribution-bundle": ">=3.0|<=5.0.5",
    "incenteev/composer-parameter-handler": "~2.0",
    "ifsnop/mysqldump-php" : "~2.1"

A la fin du composer, vous devrez saisir les parameters liés à l'application. Elle n'embarque pas de base de données, les parameters à saisir concernent la connexion à sauvegarder par défaut
    
    rma_host:           127.0.0.1
    rma_port:           3306
    rma_username:       root
    rma_password:       none
    rma_compress:       gzip
    rma_zip:            no
    rma_dir_zip:        %kernel.root_dir%/../web/zip
    rma_dir_dump:       %kernel.root_dir%/../web/dump
    rma_nb_jour:        5
    rma_nombre_dump:    10
    rma_ftp:            no
    rma_ftp_ip:         127.0.0.1
    rma_ftp_username:   rma
    rma_ftp_password:   rma_password
    rma_ftp_port:       21
    rma_ftp_timeout:    90
    rma_ftp_path:       /home/rma/dump
    rma_excludes:
        - mysql
        - performance_schema

Attention : Pour mettre un password vide, n'oubliez pas le 'none'.
Attention : Pour les dir, vous devez doubler les DIRECTORY SEPARATOR. (exemple : dir_dump=C:\\Users\\rmA\\Desktop)
Attention : Si vous ne souhaitez pas exclude de base de données dans vos dumps, inscrivez - none
Attention : Pour ne pas effacer de dump au fur et à mesure renseigner 'none' aux champs nb_jour et nombre_dump

### Les commandes :

Pour voir les commandes mises à votre disposition rendez-vous à la racine et écrivez :
    
    php app/console 

Les commandes mises à disposition sont préfixées par "rma:"
    
    ** rma:dump:database ----  Permet de réaliser un dump 
        Option :
            --one pour sauvegarder une base unique
            --i pour ouvrir l'interface d'intéractions pour les données de connexion (sinon les infos en parameters seront prises par défaut)
            --ftp permet de sauvegarder le dump en FTP. Ne fonctionne actuellement que pour une archive zippée. 
            --name permet de définir un nom custom pour le dump

    ** rma:dump:cron ---- Commande prévue spécialement pour les CRON
        Permet de réaliser un dump en crontab. Si vous ne mettez pas d'argument toutes les bases de données seront sauvegardées.
        Par défaut les paramètres sont ceux définis au niveau de votre paramters.yml
        Options : 
            --host
            --port
            --username
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

            Exemple :
                php app/console rma:dump:cron --host=127.0.0.1 --password=none --username=root --dir_zip=C:\\Users\\rmA\\Desktop\\Save

        Arguments :
        Les bases de données à extraire. Si aucun argument, toutes les bases seront sauvegardées
            Exemple avec argument : 
                php app/console rma:dump:cron rmabdd mysql --host=127.0.0.1
            Exemple sans argument : 
                php app/console rma:dump:cron 


    ** rma:dump:clean ---- Commande prévue pour nettoyer les répertoires de dump
        Permet de supprimer des dumps
        Par défaut le répertoire à vider est celui défini au niveau du parameters.yml
        Options : 
            --nb_jour ; Permet de définir en nombre de jours, la date à partir de laquelle les dump seront conservés
            --nombre ; Permet de définir le nombre de dump à conserver

            Exemple :
                php app/console rma:dump:clean --nb-jour=4 
            Tous les dates de plus de 4 jours seront supprimés
                php app/console rma:dump:clean --nombre=15
            Va conserver les 15 derniers dumps 

    ** rma:dump:sync ---- Commande pour synchroniser les logs de dump avec les dumps effectivement présents dans le répertoire de dump
        Permet notamment de mettre à jour le dossier des logs dans le cas où vous supprimeriez manuellement des dumps
        Par défaut le répertoire à vider est celui défini au niveau du parameters.yml
        Options :
            --dir_dump ; permet de définir un répertoire à gérer spécifique 
