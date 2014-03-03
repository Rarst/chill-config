# Description

Chill Config is experimental YAML config handler for WordPress.

It allows to "freeze" constants, options, transients, and filters to values sourced from YAML configuration file.

# Installation

Clone/download from repository and `composer install`.

At the top of `wp-config.php` add:

```php
// require autoload

$chill_config = new Chill_Config( __DIR__ . '/wp-config.yaml' );
```

YAML example:

```yaml
constants:
  WP_DEBUG: True
options:
  blogdescription: blog's tag line
transients:
  doesnotexist: or does it?
filters:
  browse-happy-notice: ''
```

# License Info

Chill Config own code is licensed under GPL-2.0+ and it makes use of code from:

 - WordPress (GPL-2.0+)
 - Symfony YAML (MIT)
