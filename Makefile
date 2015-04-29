SHELL = bash

all: install

install: install-server install-client build-client

install-server:
	composer install --no-dev

install-client:
	bower install --production
	npm install --no-optional

devinstall: devinstall-server devinstall-client build-client

devinstall-server:
	composer install

devinstall-client:
	bower install
	npm install

build-client: build-semantic-ui

build-semantic-ui:
	node_modules/.bin/gulp \
		--gulpfile views/semantic-ui/gulpfile.js \
		--cwd views/semantic-ui/ \
		build
	cp -v -r \
		views/semantic-ui/dist/themes/default/assets/fonts \
		public/static/vendor/semantic-ui

clean:
	rm -rf node_modules
	rm -rf views/semantic-ui/dist/
	rm -f data/variable/log/*.log

distclean: clean distclean-server distclean-client

distclean-server:
	find vendor -type d -depth 1 | \
		xargs rm -rf
	rm -f vendor/autoload.php
	find bin -type l -depth 1 | \
		xargs rm

distclean-client:
	find public/static/vendor -type d -depth 1 | \
		xargs rm -rf

uninstall:
	rm -f data/etc/configuration/server.json
	rm -f data/variable/database/katana_*.sqlite
