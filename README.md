## Prérequis 

Pour réaliser la suite de ce guide, vous aurez besoin de :
- [Vagrant](https://www.vagrantup.com/downloads.html)
- [Ansible](http://docs.ansible.com/ansible/latest/installation_guide/intro_installation.html)
- [VirtualBox](https://www.virtualbox.org/wiki/Downloads)

Pour un fonctionnement optimal, je vous conseille de réaliser tout ce qui va suivre sur un environnement Linux.

## Configuration du Vagrantfile

Vous pouvez retrouver l'intégralité du fichier dans le repo.

On initialise le Vagrantfile avec un _Ubuntu 14.04_.
`vagrant init ubuntu/trusty64`

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
  tasks:
  ```

Pour chacune des tâches, nous précisons un nom (ce que l'on veut, cela nous permet d'identifier facilement son rôle), ainsi que le module utilisé, ici apt.    
Ce module prend en compte une série d'options auxquelles on passe des valeurs.   
Ici, on installe donc très facilement Apache2, mySql et php. 
```yaml
- hosts: all
  sudo: true
  tasks:
    - name: update apt cache
      apt: update_cache=yes
    - name: install apache
      apt: name=apache2 state=present
    - name: install mysql
      apt: name=mysql-server state=present
    - name: install php
      apt: name=php7.2 state=present
  ```