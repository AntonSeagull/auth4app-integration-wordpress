=== Auth4App Integration ===
Contributors: yourname
Tags: authentication, authorization, sms alternative, phone authentication
Requires at least: 5.0
Tested up to: 5.7
Requires PHP: 7.0
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Описание ==

Auth4App Integration - это плагин, который позволяет использовать сервис Auth4App для аутентификации по номеру телефона через мессенджеры вместо SMS.

== Установка ==

1. Загрузите файлы плагина в директорию `/wp-content/plugins/auth4app-integration` или установите плагин через экран плагинов WordPress.
2. Активируйте плагин через экран 'Плагины' в WordPress.
3. Перейдите в 'Настройки' -> 'Auth4App' и настройте плагин, введя API ключ и другие параметры.

== Часто задаваемые вопросы ==

= Как настроить плагин? =

Перейдите в 'Настройки' -> 'Auth4App' и введите ваш API ключ Auth4App и другие параметры, такие как маска номера телефона и интервал проверки статуса авторизации.

== Использование ==

1. После установки и активации плагина, перейдите в 'Настройки' -> 'Auth4App' для конфигурации.
2. Введите ваш API ключ Auth4App.
3. Установите маску для ввода номера телефона (по умолчанию: +0 (000) 000-0000).
4. Установите интервал проверки статуса авторизации (в секундах, по умолчанию: 5 секунд).
5. Сохраните изменения.

Для отображения формы аутентификации на любой странице или записи, используйте шорткод `[auth4app_form]`.

== Пример шорткода ==

Используйте следующий шорткод для отображения формы аутентификации:

    [auth4app_form]
