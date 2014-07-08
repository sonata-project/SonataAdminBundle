test:
	phpunit
	cd Resources/doc && sphinx-build -W -b html -d _build/doctrees . _build/html

bower:
	bower update
