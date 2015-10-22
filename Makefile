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

build-client: build-semantic-ui build-moment

build-semantic-ui:
	node_modules/.bin/gulp \
		--gulpfile resource/view/semantic-ui/gulpfile.js \
		--cwd resource/view/semantic-ui/ \
		build
	cp -v -r \
		resource/view/semantic-ui/dist/themes/default/assets/fonts \
		public/static/vendor/semantic-ui

build-moment:
	mkdir -p public/static/vendor/moment/
	cp \
		node_modules/moment/min/moment.min.js \
		public/static/vendor/moment/

clean:
	find . -name ".DS_Store" | \
		xargs rm -f
	rm -rf node_modules

	rm -rf resource/view/semantic-ui/dist/
	find public/static/vendor/ember \
		-type f \
		-not -name 'ember.js' -and \
		-not -name 'ember.min.js' -and \
		-not -name 'ember-template-compiler.js' | \
			xargs rm -f
	find public/static/vendor/ember-data \
		-type f \
		-not -name 'ember-data.js' -and \
		-not -name 'ember-data.js.map' -and \
		-not -name 'ember-data.min.js' | \
			xargs rm -f
	find public/static/vendor/ember-simple-auth \
		-type f \
		-not -name 'simple-auth.js' | \
			xargs rm -f
	find public/static/vendor/node-uuid \
		-not -name 'node-uuid' -and \
		-not -name 'uuid.js' | \
			xargs rm -rf
	find public/static/vendor/event-source-polyfill \
		-not -name 'event-source-polyfill' -and \
		-not -name 'eventsource.js' -and \
		-not -name 'eventsource.min.js' | \
			xargs rm -rf
	find public/static/vendor/jquery \
		-not -name 'jquery' -and \
		-not -name 'dist' -and \
		-not -name 'jquery.js' -and \
		-not -name 'jquery.min.js' -and \
		-not -name 'jquery.min.map' | \
			xargs rm -rf
	rm -f data/log/*.log

	find vendor -maxdepth 1 -type d | \
		xargs rm -rf
	rm -f vendor/autoload.php
	find bin -maxdepth 1 -type l | \
		xargs rm

	find public/static/vendor -maxdepth 1 -type d | \
		xargs rm -rf

uninstall:
	@echo 'You are going to uninstall sabre/katana and lose everything!'
	@read -p 'Are you sure? [Y/n] ' go; \
		if [[ 'Y' = $$go ]]; then \
			$(MAKE) headless-uninstall
		else \
			echo 'Aborted!'; \
		fi

headless-uninstall:
	echo 'Remove data/configuration/server.json'; \
	rm -f data/configuration/server.json; \
	echo 'Remove data/database/katana_*.sqlite'; \
	rm -f data/database/katana_*.sqlite; \
	echo 'Remove data/home/*'; \
	find data/home/* -maxdepth 1 -type d | \
		xargs rm -rf; \


test: devinstall-server
	bin/atoum \
		--configurations tests/.atoum.php \
		--bootstrap-file tests/.bootstrap.atoum.php
	bin/sabre-cs-fixer \
		fix \
			--dry-run \
			--diff \
			.

DISTFILES = .bowerrc CHANGELOG.md LICENSE Makefile README.md bin bootstrap.php bower.json composer.json composer.lock data lib package.json public resource semantic.json tests vendor
DISTPOINTLESS = .bowerrc Makefile bower.json composer.json composer.lock node_modules tests
KATANA_VERSION = $(shell php -r "include 'lib/Version.php'; echo Sabre\Katana\Version::VERSION;")

dist: dist-clean
	mkdir -p build/sabre-katana
	cp -Rv $(DISTFILES) build/sabre-katana
	$(MAKE) -C build/sabre-katana headless-uninstall
	$(MAKE) -C build/sabre-katana clean
	$(MAKE) -C build/sabre-katana all
	cd build/sabre-katana; \
		rm -r $(DISTPOINTLESS)
	cd build; zip -r sabre-katana-$(KATANA_VERSION).zip sabre-katana


dist-clean:
	rm -rf build/sabre-katana

.PHONY: all install install-server install-client devinstall devinstall-server devinstall-clietn build-semantic-ui build-moment clean distclean distclean-server distclean-client uninstall testo dist-clean
