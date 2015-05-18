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
		--gulpfile views/semantic-ui/gulpfile.js \
		--cwd views/semantic-ui/ \
		build
	cp -v -r \
		views/semantic-ui/dist/themes/default/assets/fonts \
		public/static/vendor/semantic-ui

build-moment:
	mkdir public/static/vendor/moment/
	mv \
		node_modules/moment/min/moment.min.js \
		public/static/vendor/moment/

clean:
	find . -name ".DS_Store" | \
		xargs rm -f
	rm -rf node_modules
	rm -rf views/semantic-ui/dist/
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
	@echo 'You are going to uninstall sabre/katana and lose everything!'
	@read -p 'Are you sure? [Y/n] ' go; \
		if [[ 'Y' = $$go ]]; then \
			echo 'Remove data/etc/configuration/server.json'; \
			rm -f data/etc/configuration/server.json; \
			echo 'Remove data/variable/database/katana_*.sqlite'; \
			rm -f data/variable/database/katana_*.sqlite; \
			echo 'Remove data/home/*'; \
			find data/home -type d -depth 1 | \
				xargs rm -rf; \
		else \
			echo 'Aborted!'; \
		fi

test: devinstall-server
	bin/atoum \
		--configurations tests/.atoum.php \
		--bootstrap-file tests/.bootstrap.atoum.php
	bin/sabre-cs-fixer \
		fix \
			--dry-run \
			--diff \
			.
