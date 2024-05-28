##!/bin/bash
#app/console doctrine:schema:update --force --env=test
#app/console doctrine:fixtures:load --env=test
#phpunit -c app/

#for MySQL 5.6 > version (has been bug in fixtures bundle, when try truncate database, currently it is not fixed yet)
#!/bin/bash
app/console doctrine:database:drop --force --env=test
app/console doctrine:database:create --env=test
app/console doctrine:schema:update --force --env=test
app/console doctrine:fixtures:load --env=test --append
phpunit -c app/
