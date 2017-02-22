# --- defaults
branch ?= dev
plugin_path ?= plugin
l10n_path ?= l10n/es
l10n_name ?= aplazame-es_ES
version ?= v0.5.0

# --- shell
errors = $(shell find . -type f -name "*.php" -exec php -l "{}" \;| grep "Errors parsing ";)

clean:
	@rm -rf .s3

syntax.checker:
	@if [ "$(errors)" ];then exit 2;fi

style.req:
	@composer install --no-interaction --quiet

style:
	@vendor/bin/phpcbf || :

zip:
	@mkdir -p .s3/$(s3.path)
	@cd $(plugin_path); zip -r aplazame.latest.zip *
	@mv $(plugin_path)/aplazame.latest.zip .s3/$(s3.path)

pot:
	@cd $(plugin_path); \
	xgettext -o $(l10n_path)/default.pot `find . -name "*.php"` --add-location --from-code=UTF-8 -k_e -k_x -k__ \
		--package-name=Aplazame --package-version=$(version) \
		--msgid-bugs-address="https://github.com/aplazame/woocommerce"; \
	sed --in-place $(l10n_path)/default.pot --expression=s/CHARSET/UTF-8/; \
	sed --in-place $(l10n_path)/default.pot --expression="s#\"Language-Team.*#\"Language-Team: https://github.com/aplazame/woocommerce\\\n\"#"; \
	sed --in-place $(l10n_path)/default.pot --expression="s/\"Language:.*/\"Language: en_US\\\n\"/"

po:
	@cd $(plugin_path); msgmerge $(l10n_path)/default.po $(l10n_path)/default.pot --update --no-fuzzy-matching --backup=off

mo:
	@cd $(plugin_path); msgfmt $(l10n_path)/default.po -o $(l10n_path)/$(l10n_name).mo

l10n: pot po mo

push:
	@git push origin HEAD

init.master:
	@git checkout master
	@git pull origin master

release: init.master
	@git checkout release
	@git merge master
	@git push origin release
	@git checkout $(branch)

$(branch): init.master
	@git checkout $(branch)
	@git push origin $(branch)
