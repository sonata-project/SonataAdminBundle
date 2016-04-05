phpcs:
	php-cs-fixer fix --verbose

eslint:
	npm run lint -- --fix

phpcs_dry_run:
	php-cs-fixer fix --verbose --dry-run

eslint_dry_run:
	npm run lint

cs: phpcs eslint

cs_dry_run: phpcs_dry_run eslint_dry_run

test: phpunit

docs:
	cd Resources/doc && sphinx-build -W -b html -d _build/doctrees . _build/html

phpunit:
	phpunit

bower:
	bower update
