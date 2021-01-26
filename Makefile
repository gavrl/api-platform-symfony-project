up:
	symfony server:start -d

down:
	symfony server:stop

cache:
	symfony console cache:clear

migration:
	symfony console make:migration

migrate:
	symfony console d:m:m