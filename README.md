IpPageBundle
==================

Bundle permettant d'intégrer l'outil de personnalisation de pages dans un site web

## Installation

### Etape 1

Pour installer ajouter ces lignes dans le fichier composer.json

```json
{
  "repositories": [{
    "type": "composer",
    "url": "https://www.repo.info-plus.fr/"
  }]
}
```

```json
{
    "require": {
        "ip/pagebundle" : "^1.2"
    }
}
```

```json
{
    "config": {
        "component-dir": "web/assets"
    }
}
```

Mettre à jour les vendors

```bash
composer update
```

Et activer le bundle

```php
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Ip\BibliothequeBundle\IpBibliothequeBundle(),
    new Ip\PageBundle\IpPageBundle(),
    // ...
);
```

### Etape 2

Ajouter la configuration au fichier de configuration:

``` yaml
# app/config/config.yml

ip_bibliotheque:
    assets_path: /assets
    root_folder: /bibliotheque
    file:
        file_class: namespace_of_file_entity
    folder:
        folder_class: namespace_of_folder_entity

ip_page:

```

## Etape 3


Ajouter le filtre imagine

``` yaml
# app/config/config.yml

image_1000_1000
```

Il faut aussi que jQuery UI sois chargé dans la page ou 
vous voulez utiliser le module de personnalisation de pages.

Ajouter un champ dans votre entité de page

Le parametre from correspond au nom de la variable où est
stockée le html à editer

```php
<?php
    use Ip\PageBundle\Mapping\Annotation as IpPage;

    class YourPageClass{
        // ...
        /**
         * @ORM\Column(type="text")
         * @IpPage\ToTemplate(from="description")
         */
        private $template;
        // ...
        /**
         * @return mixed
         */
        public function getTemplate()
        {
            return $this->template;
        }
    
        /**
         * @param mixed $template
         * @return Page
         */
        public function setTemplate($template)
        {
            $this->template = $template;
            return $this;
        }   
        // ...
    }
```

Ajouter le CSS à la page :

``` jinja
    {{ ipPageStyle() }}
```

## Utilisation basique

Lors de la création d'un formulaire ajouter le champ : 

``` php
<?php

use Ip\PageBundle\Form\Type\IpPageType;

public function buildForm(FormBuilder $builder, array $options)
{
    // ...
    $builder->add('page', IpPageType::class, array(
        'required' => false
    );
    // ...
}
```

Puis pour afficher dans la vue :

``` jinja
    {{ include(template_from_string(page.template)) }}
```
