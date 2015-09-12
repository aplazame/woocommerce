errors = $(shell find . -type f -name "*.php" -exec php -l "{}" \;| grep "Errors parsing ";)
l10n_path ?= l10n/es/default
files = $(shell find . -name "*.php")

test:
	@php ./test/Aplazame.php

syntax.checker:
	@if [ "$(errors)" ];then exit 2;fi

zip:
	@zip -r latest.zip .

pot:
	@xgettext --from-code=utf-8 -k_e -k_x -k__ -o $(l10n_path).pot $(files)

po:
	@msgmerge --update --no-fuzzy-matching --backup=off $(l10n_path).po $(l10n_path).pot

mo:
	@msgfmt $(l10n_path).po -o $(l10n_path).mo

push:
	@git push origin HEAD

init.master:
	@git checkout master
	@git pull origin master

release: init.master
	@git checkout release
	@git merge master
	@git push origin release
	@git checkout -b $(branch)

branch: init.master
	@git checkout -b $(branch)
