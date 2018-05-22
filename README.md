## Prérequis 

Pour réaliser la suite de ce guide, vous aurez besoin de :
- [Vagrant](https://www.vagrantup.com/downloads.html)
- [Ansible](http://docs.ansible.com/ansible/latest/installation_guide/intro_installation.html)
- [VirtualBox](https://www.virtualbox.org/wiki/Downloads)

Pour un fonctionnement optimal, je vous conseille de réaliser tout ce qui va suivre sur un environnement Linux.

## Configuration du Vagrantfile

Vous pouvez retrouver l'intégralité du fichier dans le repo.

On initialise le Vagrantfile avec un _Ubuntu 16.04_.
`vagrant init ubuntu/xenial64`

On mappe le port 80 de la Vagrant au port 8080 de notre localhost de manière à pouvoir accéder à notre serveur web une fois que celui-ci sera provisionné.
`config.vm.network "forwarded_port", guest: 80, host: 8080`

Maintenant, il faut dire à Vagrant que l'on veut utiliser Ansible comme provisionneur, on pointe donc vers le fichier **playbook.yml**.
```ruby
config.vm.provision :ansible do |ansible|
    ansible.playbook = "ansible/playbook.yml"
end
```

## Configuration du playbook

Un fichier playbook Ansible peut permettre d'effectuer des tâches sur plusieurs serveurs que l'on peut lister. Ici, on indique l'option **all** qui permet de dire à Ansible : "Effectue les tâches suivantes sur tous les serveurs que tu connais". Etant donné que notre Ansible est relié à une Vagrant, il effectuera ses tâches sur ladite Vagrant.    

Puisque nous allons effectuer des commandes systèmes nécessitant un accès privilégié, nous indiquons que les commandes seront effectuées en **sudo**.

```yaml
- hosts: all
  sudo: true
  ```

On créer des rôles qui permettent de mieux classifier nos différentes tâches, installations et configurations. Dans notre cas, on créer deux rôles : **webserver** et **database**. Chaque rôle est représenté dans par un dossier du même nom. Il nous suffit ensuite d'inclure ces rôles dans le fichier _playbook.yml_.

```yaml
- hosts: all
  sudo: true
  vars:
    document_root: /vagrant
  pre_tasks:
    - name: update apt cache
      apt: update_cache=yes
  roles:
    - webserver
    - database
```

Il faut savoir qu'Ansible exécute d'abord les rôles avant les tâches décrite dans le fichier playbook.yml. C'est pourquoi ici nous plaçons l'update du cache en **pre_tasks**. Ainsi, toutes les tâches ici présentes s'effectueront en premier. 

On a donc un dossier _roles_ dans lequel on retrouve les dossiers _webserver_ et _database_, qui sont nos rôles.   
Pour chaque rôle, Ansible va regarder dans le fichier situé dans _my-role-dir/tasks/**main.yml**_ pour connaître les tâches à exécuter. Dans l'exemple de notre rôle webserver, nous incluons les fichiers _apache.yml_ et _php.yml_, qui se trouvent dans le même dossier.

```yaml
- include: apache.yml
- include: php.yml
```

En soit, il serait tout à fait possible d'écrire directement toutes les tâches que l'on veut exécuter dans le fichier _main_, mais nous préférons séparer l'installation et la configuration d'apache et de php. De manière générale, c'est une bonne pratique, pour des raisons d'évolutivité et de modularité.

 ```yaml
# php.yml

- name: install php
  apt: name=php7.0 state=present
 ```

  ```yaml
# apache.yml

- name: install php
  apt: name=php7.0 state=present
 ```



 , nous précisons un nom (ce que l'on veut, cela nous permet d'identifier facilement son rôle), ainsi que le module utilisé, ici apt.    
Ce module prend en compte une série d'options auxquelles on passe des valeurs.   
Ici, on installe donc très facilement Apache2, mySql et php. 
