# migrate-s3-nc
It's a tool to migrate a structured database to S3 storage.

# Before run

First, you **must** stop the nextcloud service and **backup** your database.
Then, you must copy the `.env-sample` to `.env` file and you must define your config in `.env` file.

# `.env` file

This the content `.env-sample` file you must copy to `.env-sample` :

```ini
# Nextcloud Informations
NEXTCLOUD_FOLDER_PATH=""
NEXTCLOUD_CONFIG_PATH="/config/config.php"

# MYSQL Information
MYSQL_DATABASE_SCHEMA=""
MYSQL_DATABASE_USER=""
MYSQL_DATABASE_PASSWORD=""
MYSQL_DATABASE_HOST=""


# S3 Information
S3_ENDPOINT=""
S3_REGION=""
S3_KEY=""
S3_SECRET=""
S3_TIME_ZONE=""
S3_BUCKET_NAME=""
```

- `NEXTCLOUD_FOLDER_PATH` : Is the data folder where the files are stored
- `NEXTCLOUD_CONFIG_PATH` : Is the config file of Nextcloud. It's the only file to not modif
- `MYSQL_DATABASE_SCHEMA` : The database name
- `MYSQL_DATABASE_USER` : The applicative user to connect to database
- `MYSQL_DATABASE_PASSWORD` : The password of your applicative user
- `MYSQL_DATABASE_HOST` : The hostname of your database (only `local` works)
- `S3_ENDPOINT` : The URL of your object storage server
- `S3_REGION` : The region of you object storage server
- `S3_KEY` : The key to use object storage server's API
- `S3_SECRET` : 
- `S3_TIME_ZONE`
- `S3_BUCKET_NAME`

# How to run ?

You must run this command to starting the migration :

```bash
$ php main.php
```

# Dependencies

- Dotenv : To set your config.
- Aws SDK Php : To push your files.