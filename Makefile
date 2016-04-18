detect_long_lines_in_diff:
	@# Check Added, Copied, Modified or Renamed files only \
	if git diff --cached --diff-filter=ACMR 'HEAD^' | \
		# Check added lines only \
		grep --extended-regexp '^\+' | \
		# Remove file header \
		grep --invert-match --extended-regexp '^\+\+\+' | \
		# Find lines longer than 120 characters \
		grep --extended-regexp '.{121}'; \
	then echo 'long lines detected'; exit 1; \
	else exit 0; \
	fi
cs:
	php-cs-fixer fix --verbose

cs_dry_run:
	php-cs-fixer fix --verbose --dry-run

test_cs: detect_long_lines_in_diff cs_dry_run

test:
	phpunit

docs:
	cd Resources/doc && sphinx-build -W -b html -d _build/doctrees . _build/html

bower:
	bower update
