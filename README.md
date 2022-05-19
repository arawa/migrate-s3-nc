# migrate-s3-nc

It's a tool to migrate a structured database to S3 storage.

# Before run

First, you must build with composer command-line : `composer install` from the project folder. If you have not composer, follow this instruction page [to install composer](https://getcomposer.org/download/) . Then, you **must** stop the web service, the Nextcloud's **maintenance** and **backup** your database.
Then, you must copy the `.env-sample` to `.env` file and you must define your config in `.env` file.

## Step 1 : Disabled your web service

```bash
# nginx
sudo systemctl stop nginx.service
# apache
sudo systemctl stop apache.service
# or this
sudo systemctl stop apache2.service
```

## Step 2 : Check your files & database

```bash
# scan your filesystem
sudo -u <web-service> php occ files:scan --all
# cleanup your database
sudo -u <web-service> php occ files:cleanup
```

**Remark** : You must replace `<web-service>` by your web service like : nginx, apache and so on.

## Step 3 : Comment your cron tasks for Nextcloud

```bash
# nginx
sudo crontab -u nginx -e
# apache
sudo crontab -u apache -e
# or this
sudo crontab -u apache2 -e
```

## Step 4 : Enable the Nextxcloud's maintenance

```bash
cd /var/www/html/my-nextcloud-instance
sudo -u <web-user> php occ maintenance:mode --on
```

**Remark** : The `<web-user>` is the user of your web server : `nginx` for the nginx, `www-data` for the apache and so on.

This step is very important. Otherwise, your Nextcloud will recreate the storages in the `oc_storages` database table.

## Step 5 : backup your database

```bash
mysqldump --user <user-database> --password <database-name> > <database-name>-backup.sql
```

Now, you can configure your `.env` file.

## Configure `.env` file

This the content `.env-sample` file you must copy to `.env-sample` :

```ini
# Nextcloud Informations
NEXTCLOUD_FOLDER_PATH=""
NEXTCLOUD_CONFIG_PATH="/config/config.php"

# MYSQL Informations
MYSQL_DATABASE_SCHEMA=""
MYSQL_DATABASE_USER=""
MYSQL_DATABASE_PASSWORD=""
MYSQL_DATABASE_HOST=""

#########################################
#                                       #
# You should choice between "S3 Swift"  #
# and "S3 Anothers Informations" !      #
#                                       #
# Not both !                            #
#                                       #
#########################################

# Required
S3_PROVIDER_NAME=""
S3_ENDPOINT=""
S3_REGION=""
S3_KEY=""
S3_SECRET=""
S3_BUCKET_NAME=""

# S3 SWift Informations (Example: OVH, OpenStack)
S3_SWIFT_URL=""
S3_SWIFT_USERNAME=""
S3_SWIFT_PASSWORD=""
S3_SWIFT_ID_PROJECT=""

# S3 Anothers Informations
S3_HOSTNAME=""
S3_PORT=""

```

Nextcloud informations :

- `NEXTCLOUD_FOLDER_PATH` : Is the data folder where the files are stored
- `NEXTCLOUD_CONFIG_PATH` : Is the config file of Nextcloud. It's the only file to not modif

MySQL informations :

- `MYSQL_DATABASE_SCHEMA` : The database name
- `MYSQL_DATABASE_USER` : The applicative user to connect to database
- `MYSQL_DATABASE_PASSWORD` : The password of your applicative user
- `MYSQL_DATABASE_HOST` : The hostname of your database (only `local` works)

S3 Informations :

These env vars are required

- `S3_PROVIDER_NAME` : The provider name. For example : `OVH`, `SWIFT`, `Scaleway`, `AWS`, `Dell`, and so on.
- `S3_ENDPOINT` : The URL of your object storage server
- `S3_REGION` : The region of you object storage server
- `S3_KEY` : The key to use object storage server's API
- `S3_SECRET` : The secret which is with the `S3_KEY`
- `S3_BUCKET_NAME` : The bucket name in your S3 server

These env vars are required if you use SWIFT (`ovh`, `swift`, `openstack`)

- `S3_SWIFT_URL` : 
- `S3_SWIFT_USERNAME` :
- `S3_SWIFT_PASSWORD` :
- `S3_SWIFT_ID_PROJECT` :

For example :

```ini
# Nextcloud Informations
NEXTCLOUD_FOLDER_PATH="/var/www/html/my-nextcloud"
NEXTCLOUD_CONFIG_PATH="/config/config.php"

# MYSQL Information
MYSQL_DATABASE_SCHEMA="nextcloud"
MYSQL_DATABASE_USER="nextcloud"
MYSQL_DATABASE_PASSWORD="123456789"
MYSQL_DATABASE_HOST="localhost"

#########################################
#                                       #
# You should choice between "S3 Swift"  #
# and "S3 Anothers Informations" !      #
#                                       #
# Not both !                            #
#                                       #
#########################################

# Required
S3_PROVIDER_NAME="OVH"
S3_ENDPOINT="https://s3.par.cloud.ovh.net"
S3_KEY="dj9dalxnxxdock1xnij4k5tl0aent22v"
S3_SECRET="gtqkfradr7905d7sl6jxkwm2i0ll5ankg"
S3_BUCKET_NAME="my-bucket"
S3_REGION="par"

# S3 SWift Informations (Example: OVH, OpenStack)
S3_SWIFT_URL="https://auth.cloud.ovh.net/v3"
S3_SWIFT_USERNAME="my-user"
S3_SWIFT_PASSWORD="Nq2jjNpTfQFiLMYUCGW8NcNvtf3nHVe2"
S3_SWIFT_ID_PROJECT="5121915117413912"

# S3 Anothers Informations
S3_HOSTNAME=""
S3_PORT=""
```

After all these, you can go in the `How to run ?` part !

# <a name="how-to-run"/> How to run ?

You must run this command to starting the migration :

```bash
$ php main.php
```

After its execution, it prints this message :

```
Congrulation ! The migration is done !
You should move the new_config.php file and replace Nextcloud's config.php file with it
Please, check if it's new config is correct !
```

It means the script has run with success !

You must check the good config in the `new_config.php` file before replace it with the Nextcloud's `config.php` file.

Once the copy is done. You can check in your `oc_storages` database table if it's correct :

```sql
select * from oc_storages;
+------------+--------------------------------------+-----------+--------------+
| numeric_id | id                                   | available | last_checked |
+------------+--------------------------------------+-----------+--------------+
|          1 | object::user:user-1                  |         1 |         NULL |
|          2 | object::store:my-bcket               |         1 |         NULL |
|          3 | object::user:user-2                  |         1 |         NULL |
|          4 | object::user:user-3                  |         1 |         NULL |
|          5 | object::user:user-4                  |         1 |         NULL |
|          6 | object::user:user-5                  |         1 |         NULL |
|          7 | object::user:user-6                  |         1 |         NULL |
|          8 | object::user:user-7                  |         1 |         NULL |
|          9 | object::user:user-8                  |         1 |         NULL |
|         10 | object::user:user-9                  |         1 |         NULL |
|         11 | object::user:user-10                 |         1 |         NULL |
|         12 | object::user:user-11                 |         1 |         NULL |
|         13 | object::user:user-12                 |         1 |         NULL |
+------------+--------------------------------------+-----------+--------------+
```


Then, you must :

1. Decomment cron tasks
2. Disable Nextcloud's maintenance
3. Enable your web server (nginx, apache and so on)


# Dependencies

- Dotenv : To set your config.
- Aws SDK Php : To push your files.

# How to purge the bucket ?

You can purge your bucket with the purgeBucket.php file.
It's very useful when your migration has been stopped abruptly.

```bash
php purgeBucket.php
```

🚨 Caution : This program deletes all objects in your bucket and you must rollback your database, in particular the `oc_storage` database table. Then, begin the migration from [How to run ?](#how-to-run) part.

# Credits

The initial code of this software has been developed by [Arawa](https://www.arawa.fr/) with the financial support of [Sorbonne Université](https://www.sorbonne-universite.fr/).

<div align="center" width="100%">
    <div width="60%;">
        <img src="./images/Logo_SU_horiz_RVB_color.svg" width="20%">
        <img src="./images/logo_arawa.svg" width="30%">
    </div>
</div>