.PHONY: install
install:
	composer install

.PHONY: migrations
migrations:
	php artisan make:migration $(name)

.PHONY: migrate
migrate:
	php artisan migrate

.PHONY: seed
seed:
	php artisan db:seed

.PHONY: fresh
fresh:
	php artisan migrate:fresh --seed

.PHONY: install-pre-commit
install-pre-commit:
	pre-commit uninstall || true; pre-commit install

.PHONY: update
update: install migrate install-pre-commit ;

.PHONY: shell
shell:
	php artisan tinker

.PHONY: dbshell
dbshell:
	mysql -u$(DB_USERNAME) -p$(DB_PASSWORD) $(DB_DATABASE)

.PHONY: serve
serve:
	php artisan serve

.PHONY: lint
lint:
	composer run-script phpcs

.PHONY: superuser
superuser:
	php artisan make:user --admin

.PHONY: test
test:
	php artisan test --parallel

.PHONY: lint-and-test
lint-and-test: lint test ;

.PHONY: model
model:
	php artisan make:model $(model) --migration
