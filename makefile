# Run Pint
pint:
	./vendor/bin/pint

rector:
	./vendor/bin/rector process --dry-run
	./vendor/bin/rector process