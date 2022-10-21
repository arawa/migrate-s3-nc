#!/bin/bash

path_nextcloud="$1"
user_web="$2"

if [ $# -lt 2 ]; then
    echo -e "No arguments supplied \n"
    echo "Please, input the first argument must be the path of your instance and the user web for the second argument."
    echo ""
    echo -e "Example for nginx :\n\n\t./files_scan.sh /var/www/html/my-instance nginx"
    echo ""
    echo -e "Example for apache under CentOS, RedHat and so on :\n\n\t./files_scan.sh /var/www/html/my-instance www-data"
    echo ""
    echo -e "Example for apache under Ubuntu, Debian and so on :\n\n\t./files_scan.sh /var/www/html/my-instance apache"
    exit 1
fi

uids=($(sudo -u $user_web php $path_nextcloud/occ user:list --output=json \
    | jq -c 'keys' \
    | jq -r \
    | tr -d '["],'
))

echo -e "\n\nFiles scan for all users is in progress\n\n"
for uid in ${uids[@]}; do
    sudo -u $user_web php $path_nextcloud/occ files:scan $uid
done

echo -e "\n\nIt's done !"