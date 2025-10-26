<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 INFOTECH TEST</h1>
    <br>
</p>

QUICK START
-----------

Follow these steps to run this project locally:

1) Environment

Copy .env.dist to .env and set values
~~~
cp .env.dist .env
~~~

2) Up project
~~~
docker-compose up -d
~~~

3) Install dependencies
~~~
docker-compose exec php composer install
~~~

4) Initialize database and RBAC
~~~
# RBAC tables (Yii built-in migrations)
docker-compose exec php php yii migrate --migrationPath=@yii/rbac/migrations --interactive=0

# Project migrations
docker-compose exec php php yii migrate --interactive=0

# Seed RBAC roles/permissions
docker-compose exec php php yii rbac/init
~~~
