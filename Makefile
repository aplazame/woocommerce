# --- defaults
branch ?= dev
l10n_path ?= l10n/es
l10n_name ?= aplazame-es_ES
version ?= v0.2.0

# --- shell
files = $(shell find . -name "*.php")
errors = $(shell find . -type f -name "*.php" -exec php -l "{}" \;| grep "Errors parsing ";)

clean:
	@rm -rf .s3
	@rm -rf .wpcs

syntax.checker:
	@if [ "$(errors)" ];then exit 2;fi

style.req:
	@composer create-project wp-coding-standards/wpcs:dev-master\
		.wpcs --no-dev --no-interaction --quiet

style:
	@.wpcs/vendor/bin/phpcbf --standard=WordPress * || :

zip:
	@mkdir -p .s3/$(s3.path)
	@zip -r .s3/$(s3.path)aplazame.latest.zip *

pot:
	@xgettext -o $(l10n_path)/default.pot $(files) --add-location --from-code=UTF-8 -k_e -k_x -k__ \
		--package-name=Aplazame --package-version=$(version) \
		--msgid-bugs-address="https://github.com/aplazame/woocommerce"
	@sed --in-place $(l10n_path)/default.pot --expression=s/CHARSET/UTF-8/
	@sed --in-place $(l10n_path)/default.pot --expression="s#\"Language-Team.*#\"Language-Team: https://github.com/aplazame/woocommerce\\\n\"#"
	@sed --in-place $(l10n_path)/default.pot --expression="s/\"Language:.*/\"Language: en_US\\\n\"/"

po:
	@msgmerge $(l10n_path)/default.po $(l10n_path)/default.pot --update --no-fuzzy-matching --backup=off

mo:
	@msgfmt $(l10n_path)/default.po -o $(l10n_path)/$(l10n_name).mo

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
