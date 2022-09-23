#!/usr/bin/env bash
. $(dirname $0)/functions.fnsh
CHECKER_VERSION="$1"
CHECKER_OS="$2"
cd ${EZROOT}
wget https://github.com/fabpot/local-php-security-checker/releases/download/v${CHECKER_VERSION}/local-php-security-checker_${CHECKER_VERSION}_${CHECKER_OS} -O security-checker
chmod a+x security-checker
./security-checker
pwd
rm security-checker