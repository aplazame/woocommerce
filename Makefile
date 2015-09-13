# --- defaults
branch ?= dev
l10n_path ?= l10n/es
l10n_name ?= aplazame-es_ES

# --- shell
files = $(shell find . -name "*.php")
errors = $(shell find . -type f -name "*.php" -exec php -l "{}" \;| grep "Errors parsing ";)


test:
	@php ./test/Aplazame.php

syntax.checker:
	@if [ "$(errors)" ];then exit 2;fi

style:
	@.wpcs/vendor/bin/phpcbf --standard=WordPress * || :

zip:
	@zip -r latest.zip . -x .wpcs

pot:
	@xgettext --from-code=utf-8 -k_e -k_x -k__ -o $(l10n_path).pot $(files)

po:
	@msgmerge --update --no-fuzzy-matching --backup=off $(l10n_path)/default.po $(l10n_path)/default.pot

mo:
	@msgfmt $(l10n_path)/default.po -o $(l10n_path)/$(l10n_name).mo

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
