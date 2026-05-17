#!/usr/bin/env bash
# Installe la suite de tests WordPress + une instance WP minimale pour BDD.
# Usage : bin/install-wp-tests.sh <db_name> <db_user> <db_pass> <db_host> <wp_version>

set -e

if [ $# -lt 3 ]; then
	echo "usage: $0 <db_name> <db_user> <db_pass> [<db_host>] [<wp_version>]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress}

download() {
	if command -v curl >/dev/null 2>&1; then
		curl -fsSL -o "$2" "$1"
	else
		wget -nv -O "$2" "$1"
	fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
	WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION == 'latest' ]]; then
	WP_VERSION=$(curl -s https://api.wordpress.org/core/version-check/1.7/ | grep -o '"version":"[^"]*"' | head -1 | cut -d'"' -f4)
	WP_TESTS_TAG="tags/$WP_VERSION"
else
	WP_TESTS_TAG="tags/$WP_VERSION"
fi

install_wp() {
	if [ -d "$WP_CORE_DIR" ]; then return; fi
	mkdir -p "$WP_CORE_DIR"
	download "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz" /tmp/wordpress.tar.gz
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C "$WP_CORE_DIR"
	download https://raw.githubusercontent.com/markoheijnen/wp-mysqli/master/db.php "$WP_CORE_DIR/wp-content/db.php" || true
}

install_test_suite() {
	if [ -d "$WP_TESTS_DIR" ]; then return; fi
	mkdir -p "$WP_TESTS_DIR"
	svn co --quiet "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
	svn co --quiet "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/"     "$WP_TESTS_DIR/data"
	download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':"     "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/youremptytestdbnamehere/$DB_NAME/"  "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/yourusernamehere/$DB_USER/"          "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/yourpasswordhere/$DB_PASS/"          "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s|localhost|$DB_HOST|"                 "$WP_TESTS_DIR/wp-tests-config.php"
}

install_db() {
	# Recréer la DB de test à chaque run (idempotent).
	mysqladmin -u "$DB_USER" --password="$DB_PASS" --host="$DB_HOST" -f drop "$DB_NAME" 2>/dev/null || true
	mysqladmin -u "$DB_USER" --password="$DB_PASS" --host="$DB_HOST"    create "$DB_NAME"
}

install_wp
install_test_suite
install_db

echo "✅ WP $WP_VERSION + test suite installés."
