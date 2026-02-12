# Project Overview

Это CRM нашей компании – «Школа на Газетном» (ранее «ЕГЭ-Центр»). 
Мы — образовательная организация, осущевствляющая свою деятельность по следующим направлениям:
* Школа экстернат
* Старшая школа
* Курсы подготовки к ОГЭ
* Курсы подготовки к ЕГЭ

Основные задачи ЕГЭ-Центра: дать качественную подготовку к экзамену в формате ОГЭ/ЕГЭ, выработать навыки эффективной техники их написания, оказать психологическую поддержку во время подготовки и сдачи экзамена. Ежегодно ЕГЭ-Центр выпускает около 1000 учеников, которые поступают на бюджетные и платные отделения в различные вузы Москвы и других городов, в том числе и зарубежные. В штате ЕГЭ-Центра работает около 60 преподавателей.

CRM также выполняет функцию личного кабинета для преподавателей, учеников и их родителей.

### Ученики и их представители (clients & representatives)
В личном кабинете могут наблюдать расписание занятий, график оплаты по договору, читать отчеты преподавателей, смотреть оценки, домашние задания и т.д.

### Преподаватели (teachers)
В личном кабинете могут видеть группы, в которых они ведут занятия, своё расписание, писать отчеты, выполнять "проводку занятия" (проводка занятия – это "регистрация" занятия, учитель отмечает кто был/не был/опоздал, ставит оценки и т.д.)

### Сотрудники и администраторы (users)
Это штатные сотрудники компании. Они пользуются системой как CRM: обрабатывают входящие заявки, могут наблюдать за всеми группами, клиентами и многое другое. Ей пользуются как менеджеры по работе с клиентами, так и бухгалтеры (вносят платежную информацию), так и администраторы системы (смотрят общую статистику и тд)

## Architecture
- Monorepo structure.
- API-first design (Laravel serves JSON, Vue consumes it).
- MySQL database

## Directory Mapping
- **/back**: Contains the Laravel backend. Look here for controllers, models, migrations, and artisan commands.
- **/front**: Contains the Vue.js frontend. Look here for components, stores, and assets.

### back
- Laravel 11
- PHP 8.4
- Locally served via Sail (Docker)
- Used for API-responses only

### front
- Vue 3 (Composition API)
- Nuxt 3
- Vuetify 3 is used as UI library
- bun is used as a package manager
- node v20

## Context Rules
1. When asked about UI/components, ONLY context search in `/front`.
2. When asked about API/Database/Logic, ONLY context search in `/back`.
3. Do not scan root files for code logic, they are only for config (docker, git, etc).

## Production URLs
- backend base url: api.max-flex.ru
- frontend base url: max-flex.ru

### Local testing
1) For local in-browser testing via Playwright MCP you can use the following login data:
phone: `9252727210`
code: `1111`

2) You can change real data in database - that's okay. No need to revert changes.

3) Since we use Laravel Sail locally use should run Laravel commands in docker container using `sart` (short for "sail php artisan")