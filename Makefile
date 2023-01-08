DEFAULT_GOAL := help

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z0-9_-]+:.*?##/ { printf "  \033[36m%-27s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ [Docker] Build / Infrastructure

.PHONY: up
up: docker-up ## запускает docker-up

.PHONY: down
down: docker-down  ## запускает docker-down

.PHONY: restart
restart: docker-down docker-up  ## запускает docker-down docker-up

.PHONY: test
test: manager-test  ## запускает manager-test

.PHONY:
init: docker-down-clear manager-clear docker-pull docker-build docker-up manager-init

.PHONY: clear
clear:  ## очистка кеша
	docker-compose run --rm  manager-php-cli php bin/console cache:clear

.PHONY: rmvendor
rmvendor:  ## удаление папки vendor
	sudo rm -rf manager/vendor

.PHONY: docker-up
docker-up:  ## запускает docker-compose
	docker-compose up -d

.PHONY: docker-down
docker-down:  ## останавливает docker-compose
	docker-compose down --remove-orphans

.PHONY: docker-down-clear
docker-down-clear:  ## останавливает docker-compose c удалением томов
	docker-compose down -v --remove-orphans

.PHONY: docker-pull
docker-pull:  ## скачивает нужные образы
	docker-compose pull

.PHONY: docker-build
docker-build:  ## запускает контейнеры образы
	docker-compose build

.PHONY: manager-init
manager-init: manager-composer-install manager-assets-install manager-wait-db manager-migrations manager-fixtures manager-ready  ## инициализирует докер

manager-clear:
	docker run --rm -v ${PWD}/manager:/app --workdir=/app alpine rm -f .ready

.PHONY: manager-assets-install
manager-assets-install:  ## yarn install и компиляция sass
	docker-compose run --rm manager-node yarn install --ignore-engines
	docker-compose run --rm manager-node npm rebuild node-sass

.PHONY: manager-composer-update
manager-composer-update:  ## composer update
	docker-compose run --rm manager-php-cli composer update

.PHONY: manager-composer-install
manager-composer-install:  ## composer install
	docker-compose run --rm manager-php-cli composer install

.PHONY: manager-wait-db
manager-wait-db:  ## ожидание перед применением фикстур
	until docker-compose exec -T manager-postgres pg_isready --timeout=0 --dbname=app ; do sleep 1 ; done

.PHONY: manager-migrations
manager-migrations:  ## запуск миграций
	docker-compose run --rm manager-php-cli php bin/console doctrine:migrations:migrate --no-interaction

.PHONY: manager-migration-rollback
manager-migration-rollback:  ## откат последней миграции
	docker-compose run --rm manager-php-cli php bin/console doctrine:migrations:migrate prev --no-interaction

.PHONY: manager-fixtures
manager-fixtures: ## запуск фикстур
	docker-compose run --rm manager-php-cli php bin/console doctrine:fixtures:load --no-interaction

manager-ready:
	docker run --rm -v ${PWD}/manager:/app --workdir=/app alpine touch .ready

.PHONY: manager-make-migrations
manager-make-migrations:  ## создаёт файлы миграций
	docker-compose run --rm manager-php-cli php bin/console make:migration

.PHONY: manager-test
manager-test:  ## запуск тестов phpunit
	docker-compose run --rm manager-php-cli php bin/phpunit

.PHONY: manager-assets-dev
manager-assets-dev:  ## npm run dev
	docker-compose run --rm manager-node npm run dev

.PHONY: manager-assets-watch
manager-assets-watch:  ## npm run watch
	docker-compose run --rm manager-node npm run watch

.PHONY: routes
routes:  ## просмотр списка роутов
	docker-compose run --rm manager-php-cli php bin/console debug:router


build-production:
	docker build --pull --file=manager/docker/production/nginx.docker --tag ${REGISTRY_ADDRESS}/manager-nginx:${IMAGE_TAG} manager
	docker build --pull --file=manager/docker/production/php-fpm.docker --tag ${REGISTRY_ADDRESS}/manager-php-fpm:${IMAGE_TAG} manager
	docker build --pull --file=manager/docker/production/php-cli.docker --tag ${REGISTRY_ADDRESS}/manager-php-cli:${IMAGE_TAG} manager
	docker build --pull --file=manager/docker/production/postgres.docker --tag ${REGISTRY_ADDRESS}/manager-postgres:${IMAGE_TAG} manager
	docker build --pull --file=manager/docker/production/redis.docker --tag ${REGISTRY_ADDRESS}/manager-redis:${IMAGE_TAG} manager

push-production:
	docker push ${REGISTRY_ADDRESS}/manager-nginx:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/manager-php-fpm:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/manager-php-cli:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/manager-postgres:${IMAGE_TAG}
	docker push ${REGISTRY_ADDRESS}/manager-redis:${IMAGE_TAG}

deploy-production:
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'rm -rf docker-compose.yml .env'
	scp -o StrictHostKeyChecking=no -P ${PRODUCTION_PORT} docker-compose-production.yml ${PRODUCTION_HOST}:docker-compose.yml
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "REGISTRY_ADDRESS=${REGISTRY_ADDRESS}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "IMAGE_TAG=${IMAGE_TAG}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_APP_SECRET=${MANAGER_APP_SECRET}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_DB_PASSWORD=${MANAGER_DB_PASSWORD}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_REDIS_PASSWORD=${MANAGER_REDIS_PASSWORD}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'echo "MANAGER_OAUTH_FACEBOOK_SECRET=${MANAGER_OAUTH_FACEBOOK_SECRET}" >> .env'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'docker-compose pull'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'docker-compose --build -d'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'until docker-compose exec -T manager-postgres pg_isready --timeout=0 --dbname=app ; do sleep 1 ; done'
	ssh -o StrictHostKeyChecking=no ${PRODUCTION_HOST} -p ${PRODUCTION_PORT} 'docker-compose run --rm manager-php-cli php bin/console doctrine:migrations:migrate --no-interaction'