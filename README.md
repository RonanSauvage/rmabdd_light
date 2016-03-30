rmABDD_console.
Ce projet est une adaptation light de rmaBDD. Elle n'intègre que la partie console.

## Pour initialiser le projet :

Placez-vous à la racine du projet et lancez la commande : 

    composer install

(si vous n'avez pas le client composer, le phar n'est pas versionné, utilisez)

    php composer.phar install

A la fin du composer, vous devrez saisir les parameters liés à l'application. Elle n'embarque pas de base de données, les parameters à saisir concernent la connexion à sauvegarder par défaut
    
    rma_host:           127.0.0.1
    rma_port:           3306
    rma_username:       root
    rma_password:       ''
    rma_compress:       gzip
    rma_zip:            false
    rma_dir_zip:        %kernel.root_dir%/../web/zip
    rma_dir_dump:       %kernel.root_dir%/../web/dump
    rma_ftp_ip:         127.0.0.1
    rma_ftp_username:   rma
    rma_ftp_password:   rma_password
    rma_ftp_port:       21
    rma_ftp_timeout:    90
    rma_ftp_path:       /home/rma/dump

Attention : Pour mettre un password vide, n'oubliez pas les deux côtes.
Attention : Pour les dir, vous devez doubler les DIRECTORY SEPARATOR. (exemple : dir_dump=C:\\Users\\rmA\\Desktop)

## Les commandes :

Pour voir les commandes mises à votre disposition rendez-vous à la racine et écrivez :
    
    php app/console 

Les commandes de cette application sont préfixées par "rma:"
    
    rma:dump:database ----  Permet de réaliser un dump 
        Option :
            --not-all pour ne pas sauvegarder toutes les bases
            --i pour ouvrir l'interface d'intéractions pour les données de connexion (sinon les infos en parameters seront prises par défaut)
            --ftp permet de sauvegarder le dump en FTP. Ne fonctionne actuellement que pour une archive zippée. 
        

    rma:dump:cron ---- Commande prévue spécialement pour les CRON
        Permet de réaliser un dump en crontab. Si vous ne mettez pas d\'argument toutes les bases de données seront sauvegardées.
        Par défaut les paramètres sont ceux définis au niveau de votre paramters.yml
        Options : 
            --host
            --port
            --username
            --password  (pour mettre un mot de passe vide, mettez le paramètre --password=none)
            --compress  {none, gzip, bzip2}
            --zip       {true, false}
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