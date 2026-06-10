# Run Pint
pint:
	./vendor/bin/pint

rectord:
	./vendor/bin/rector process --dry-run

rector:
	./vendor/bin/rector process

phpstan:
	docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=1G