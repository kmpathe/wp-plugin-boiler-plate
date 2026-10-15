# WP Plugin Boiler Plate

Boilerplate basé sur le plugin source : classes PSR-4, méthodes camelCase, SingletonTrait, Admin, Services, Config et vues séparées.

## Installation

Installer le ZIP dans Extensions > Ajouter > Téléverser puis activer.
WordPress 6.5 minimum et PHP 8.1 minimum. Aucun build ni installation Composer nécessaire.
La page est directement accessible dans le menu principal de WordPress : WP Plugin Boiler Plate. Les champs « Demo text » et « Demo number » utilisent les clés `demoText` et `demoNumber` dans l'option globale `wp-plugin-boiler-plate_settings`.

## Search/replace

- `wp-plugin-boiler-plate` : slug, text domain, options et package Composer.
- `WP Plugin Boiler Plate` : nom affiché, orthographe volontaire.
- `WpPluginBoilerPlate` : namespace et classe principale.
- `WP_PLUGIN_BOILER_PLATE` : constantes du bootstrap.

Renommer aussi le dossier racine, le fichier wp-plugin-boiler-plate.php et src/WpPluginBoilerPlate.php si la classe change.
Après modification du namespace, lancer `composer dump-autoload` si Composer est utilisé.

## Structure et conventions

Le chargeur PSR-4 autonome permet une activation directe. `composer install` génère un chargeur Composer, utilisé en priorité lorsqu'il existe.

Toute logique métier a été retirée : API sportives, tables, migrations, cron, authentification, routes REST, cache et mises à jour externes.
Les commandes npm compilent les assets admin et public séparément. Utiliser du JS moderne sans jQuery.

La Settings API WordPress gère l'enregistrement avec nonce et capacité manage_options. Le texte est nettoyé par sanitize_text_field et échappé à l'affichage.
Références : https://developer.wordpress.org/reference/functions/register_setting/ et https://developer.wordpress.org/reference/functions/add_menu_page/.

Aucun hook d'activation ou de désactivation vide. L'option est conservée à la désactivation et à la suppression.

## Formulaire global

Tous les champs du formulaire sont regroupés dans un tableau WordPress unique : `wp-plugin-boiler-plate_settings`.

Nommer les champs `wp-plugin-boiler-plate_settings[demoText]`, `wp-plugin-boiler-plate_settings[anotherField]`, etc. Aucun register_setting ni constante supplémentaire par champ.

Charger le tableau avec `$settings = SettingsService::getInstance()->getAll();`, puis lire `$settings['demoText']` et `$settings['demoNumber']`. Les valeurs par défaut sont une chaîne vide et 0. demoNumber est un entier signé ; les valeurs invalides reviennent à 0.

Le nettoyage global conserve les clés camelCase et nettoie les valeurs texte, y compris dans les tableaux imbriqués. Adapter sanitize() pour les futurs champs nécessitant des types spécifiques (booléens, nombres, URL, etc.). Les champs absents du formulaire ne sont pas conservés lors de sa sauvegarde ; prévoir une valeur cachée pour les cases à cocher décochées.

La valeur de l'ancienne option wp-plugin-boiler-plate_demo_text est copiée lors du premier accès admin si l'option globale n'existe pas encore. L'ancienne option est conservée.
## Assets et livraison

Node.js compatible avec @wordpress/scripts : 22.22.2+, 24.15.0+ ou 26+. Installer les dépendances avec npm install, ou npm ci avec le lockfile.

- npm run dev : compile puis surveille les sources Sass et JS admin/public.
- npm run build : minifie les assets puis crée le dossier wp-plugin-boiler-plate/ à la racine.
- npm run deploy : relance le build puis crée wp-plugin-boiler-plate.zip à la racine, prêt à importer dans WordPress.

La copie de livraison contient les PHP, src/, views/, languages/, les assets compilés et les autres assets statiques. Elle exclut node_modules, scripts, fichiers npm, Git, caches, sources JS/Sass et source maps. Les dépendances de développement restent disponibles dans le projet pour continuer à travailler. Si des dépendances PHP de production sont ajoutées à composer.json, le build les installe dans la copie avec composer install --no-dev (Composer requis dans ce cas).

Pour admin et public :

    assets/admin/src/scss/**/*.scss -> assets/admin/dist/css/**/*.min.css
    assets/admin/src/js/**/*.js -> assets/admin/dist/js/**/*.min.js
    assets/public/src/scss/**/*.scss -> assets/public/dist/css/**/*.min.css
    assets/public/src/js/**/*.js -> assets/public/dist/js/**/*.min.js

Les fichiers Sass préfixés par un underscore sont des partials : leur modification déclenche la compilation, sans CSS individuel. Les sous-dossiers sont conservés. Le JS est minifié fichier par fichier ; aucun bundler ni résolution des imports. Les sorties minifiées sans source correspondante sont supprimées lors de la compilation.

Les dossiers générés et le ZIP sont ignorés par Git. Le fichier .generated-plugin identifie le dossier de livraison pour permettre sa régénération ; il est exclu du ZIP. Le nom du dossier et du ZIP vient du slug name dans package.json : modifier aussi les entrées .gitignore lors d'un renommage.

Références : https://sass-lang.com/documentation/js-api/functions/compile/, https://nodejs.org/api/fs.html#fswatchfilename-options-listener et https://www.archiverjs.com/docs/quickstart/.

AssetsController charge admin.min.css et admin.min.js uniquement sur la page du plugin, et public.min.css/public.min.js sur le front. Les fichiers sont pris dans assets/admin/dist et assets/public/dist. Le timestamp des fichiers renouvelle le cache après compilation. Les scripts sont chargés dans le footer sans jQuery.

## Grunt et données JavaScript

Gruntfile.js pilote les tâches assets, watch, dev, build et deploy. Les commandes npm run dev/build/deploy utilisent ces tâches. scripts/assets.cjs conserve la logique de compilation et de livraison commune.

AssetsController expose window.wpPluginBoilerPlateAdmin dans admin.js et window.wpPluginBoilerPlatePublic dans public.js via wp_localize_script, après l'enregistrement du script. Chaque objet contient ajaxUrl, nonce, pluginUrl et strings.loading/strings.error.

Les actions de nonce sont wp-plugin-boiler-plate-admin et wp-plugin-boiler-plate-public. Aucun endpoint AJAX n'est ajouté ; les futurs handlers doivent vérifier le nonce et les permissions adaptées. Les options privées ne sont pas transmises au front.

Références : https://developer.wordpress.org/reference/functions/wp_localize_script/ et https://gruntjs.com/creating-tasks.

## Blocs Gutenberg

Architecture inspirée de wp-plugin-bc-stories, avec blocks/ à la place de gutenberg/ :

    blocks/
      src/
        index.js
        components/
          demo-text/
            block.json
            EditDemoText.js
            SaveDemoText.js
      build/
        index.js
        index.asset.php
    src/Controllers/
      GutenbergController.php
      Blocks/
        EditDemoText.php

index.js enregistre les composants Edit et Save depuis les métadonnées. @wordpress/scripts compile JSX et imports WordPress dans blocks/build ; index.asset.php génère les dépendances et la version du bundle. Les dépendances de développement sont installées à la racine, sans deuxième npm install dans blocks/.

GutenbergController charge le bundle partagé et enregistre les blocs avec un render_callback vers leur classe PHP dans Controllers/Blocks. EditDemoText.php produit le HTML échappé sur le front. SaveDemoText retourne null pour ce bloc dynamique.

Ajouter un bloc dans blocks/src/components, importer ses composants et métadonnées dans index.js, puis enregistrer sa classe de rendu dans GutenbergController. Le watch surveille les sources JS, JSON et Sass. Importer le Sass depuis un composant JS pour le compiler avec le bundle.

npm run dev/build/deploy compile aussi les blocs. La livraison conserve blocks/build et les block.json dans blocks/src/components, nécessaires à l'enregistrement PHP ; les sources JS/JSX et Sass sont exclues.

Le bloc Demo text possède un champ Text dans la barre latérale Block settings. Sa valeur est propre à chaque instance, indépendante des options globales.

Référence : https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/.
