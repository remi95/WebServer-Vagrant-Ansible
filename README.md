# Serveur web avec Vagrant et Ansible

Un simple `vagrant up` et vous voilà muni d'un serveur web simple et préconfiguré. 

## Sommaire

[Prérequis](#prerequis)     
[Caractéristiques du serveur](#caracteristiques-du-serveur)     
[Utilisation](#utilisation)     
[Ajout de votre site](#ajout-de-votre-site)     
- [Ajout du dossier](#ajout-du-dossier)
- [Ajout d'un virtualhost](#ajout-dun-virtualhost)
- [Accès à PhpMyAdmin](#acces-a-phpmyadmin)
- [Ajout d'un fichier SQL à importer](#ajout-dun-fichier-sql-a-importer)

## Prérequis 

Pour l'utiliser, vous aurez besoin de :
- [Vagrant](https://www.vagrantup.com/downloads.html)
- [Ansible](http://docs.ansible.com/ansible/latest/installation_guide/intro_installation.html)
- [VirtualBox](https://www.virtualbox.org/wiki/Downloads)

Pour un fonctionnement optimal, je vous conseille de réaliser tout ce qui va suivre sur un environnement **Linux**.

## Caractéristiques du Serveur

Le serveur se trouve sur un **Ubuntu 16.04**.   
Les éléments suivants sont installés :
- apache2
- libapache2-mod-php7.0
- php7.0
- php7.0-mysql
- mysql-server
- mysql-client
- phpmyadmin
- python3-dev
- python-mysqldb
- libmysqlclient-dev

On notera que le module _mod_rewrite_ d'apache est aussi activé.   
Vous disposez ainsi des éléments de bases pour faire fonctionner un serveur web.  

Les fichiers que vous éditez sur votre machine hôte sont directement **synchronisés** sur la machine virtuelle. Vous pouvez donc développer sereinement, Vagrant se charge du reste !

## Utilisation

Lancer simplement la commande suivante :    
`vagrant up`

## Ajout de votre site

#### Ajout du dossier

Par défaut, tous les fichiers se trouvant dans _sites/_ seront synchronisés sur le serveur dans _/vagrant/sites_.   
Pour placer un nouveau site sur votre serveur, vous n'avez qu'à mettre votre dossier dans _sites/**mon_site**_.  
Si toutefois vous désirez placer votre site ailleurs que dans _sites/_, vous pouvez toujours modifier le **Vagrantfile** à cet endroit : 
```ruby
  config.vm.synced_folder "sites", "/vagrant/sites"
```

#### Ajout d'un virtualhost

Il est très facile d'ajouter un virtualhost, rendez vous simplement dans le fichier _ansible/roles/webserver/vars/**main.yml**_ et ajoutez une ligne dans **apache_vhosts** en l'adaptant à votre site.
```yaml
apache_vhosts:
  - {servername: dev.example.loc, document_root: /vagrant/sites/example}
  - {servername: dev.your-servername.loc, document_root: /vagrant/sites/your-dirname}
```

Par la suite, n'oubliez pas sur votre machine hôte d'éditez le fichier _/etc/**hosts** et d'y placer la ligne suivante :
```bash
192.168.225.30	dev.your-servername.loc
```

L'adresse IP est celle donnée par défaut dans le _Vagrantfile_, mais vous pouvez la changer si vous le voulez.

#### Accès à PhpMyAdmin

PhpMyAdmin est installé sur la machine virtuelle. Pour y accéder, vous pouvez utiliser n'importe quelle adresse renseignée plus haut pour l'adresse IP du serveur suivie de **/phpmyadmin**.    
Personnalisez vos identifiants d'accès à la base de données dans le fichier _ansible/roles/database/vars/**main.yml**_.

#### Ajout d'un fichier SQL à importer

Si vous disposez d'un fichier SQL que vous souhaitez directement importer dans la base de données, vous n'avez qu'à le placer à la racine de votre site. Ensuite, rendez-vous dans _ansible/roles/database/vars/**main.yml**_ et ajoutez une ligne dans **databases** en l'adaptant à votre fichier.
```yaml
 # dirname: chemin jusqu'à votre fichier .sql à partir de sites/
 # filename: nom du fichier sans le '.sql'
databases:
  - {dirname: example-website, filename: example} 
  - {dirname: my-dir-website, filename: my-sql-filename}
  ```

Attention, si jamais vous n'avez pas placé votre site dans _sites/_, l'import ne fonctionnera pas. Je vous invite donc à modifier le fichier _ansible/roles/database/tasks/**mysql.yml**_ au niveau de la ligne **target** pour l'adapter au chemin jusqu'à votre site.
```yaml
  - name: Import database
  mysql_db:
    state: import
    name: "{{ item.filename }}"
    target: /vagrant/sites/{{item.dirname}}/{{ item.filename }}.sql
  with_items:
    - "{{databases}}"
```