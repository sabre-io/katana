SHELL = bash

all: install

install: install_server install_client

install_server:
	composer install

install_client:
	npm install
	bower install

build: install build_server build_client

build_server:
	composer install --no-dev

build_client:
	node_modules/.bin/gulp \
		--gulpfile views/semantic-ui/gulpfile.js \
		--cwd views/semantic-ui/ \
		build
