#!/bin/sh

# taken from
# https://github.com/fred-lab/Docker_Symfony/blob/master/docker/nodejs/docker-entrypoint.sh
# Si jamais tu tombe là-dessus, merci Fred ! ;)

# Functions

# Display a text with colors
display() {
	text="$1"

	case "$2" in
	"Green")
		echo -e '\033[0;32m' "###### $text  ######" '\033[0m'
		;;
	"Blue")
		echo -e '\033[0;34m' "** $text  **" '\033[0m'
		;;
	"Red")
		echo -e '\033[0;31m' "!! $text  !!" '\033[0m'
		;;
	"Brown")
		echo -e '\033[0;33m' "- $text" '\033[0m'
		;;
	"Purple")
		echo -e '\033[0;35m' "- $text" '\033[0m'
		;;
	esac
}

# Script
workdir=$(pwd)
user=$(id -u)
group=$(id -g)
# For debbuging
display "Check User ID & Group ID" "Blue"
display "Host user id : $user" "Purple"
display "Host usergroup id : $group" "Purple"

ls -la

display "Checking if Package.json is present ..." "Blue"
if [ -f "./package.json" ]; then
	display "Package.json is present" "Purple"

	display "Checking if dependencies are installed ..." "Blue"
	if [ -d "./node_modules" ]; then
		display "Dependencies are installed in ./node_modules" "Purple"
	else
		display "Dependencies are not installed in ./node_modules. Running 'yarn install' ..." "Purple"
		yarn install
	fi
else
	display "Package.json is not present ! Please provide at least a Package.json with 'npm init -y'" "Red"
fi

display "Container is ready !" "Green"

exec "$@"
