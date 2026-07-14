PHPROOT=app
WEBROOT=app
JSROOT=app
BIN_WEBPACK=npm
WEBPACK_FLAGS=run build:iservmake
WEBPACK_INTERNAL_ARGS=
ASSETS_MANIFEST=app/public/static/.vite/manifest.json
ASSETS_SRC=$(shell find app/assets -type f)
SYMFONY_USER=iserv-unificonnector

LOCALE_DISABLE_POOTLE_DOWNLOAD=1
