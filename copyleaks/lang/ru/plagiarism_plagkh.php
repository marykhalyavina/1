<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * This file containes the translations for Russian
 * @package   plagiarism_plagKh
 * @copyright 2023 plagKh
 * @author    Mariya Khalyavina
 */
defined('MOODLE_INTERNAL') || die();

$string['pluginname']  = 'Плагин для проверки плагиата';
$string['plagKh'] = 'Проверка плагиата';
$string['clstudentdisclosure'] = 'Уведомление для студентов';
$string['clstudentdisclosure_help'] = 'Этот текст будет отображаться всем студентам на странице загрузки файлов.';
$string['clstudentdisclosuredefault']  = '<span>Отправляя свои файлы, вы соглашаетесь с политикой конфиденциальности';
$string['clstudentdagreedtoeula']  = '<span>Вы согласились с политикой конфиденциальности </span>';
$string['cladminconfigsavesuccess'] = 'Настройки плагина проверки на плагиат успешно сохранены.';
$string['clpluginconfigurationtab'] = 'Настройки';
$string['cllogstab'] = 'Логи';
$string['cladminconfig'] = 'Настройки плагина проверки на плагиат';
$string['clpluginintro'] = 'Плагин для проверки на плагиат представляет собой комплексное и точное решение, которое помогает преподавателям и студентам проверить оригинальность своего контента.</br></br>';
$string['clenable'] = 'Включить плагин';
$string['clenablemodulefor'] = 'Включить плагин для {$a}';
$string['claccountconfig'] = "plagkh account configuration";
$string['clapiurl'] = 'plagkh API-URL';
$string['claccountkey'] = "plagkh key";
$string['claccountsecret'] = "plagkh secret";
$string['clallowstudentaccess'] = 'Allow students access to plagiarism reports';
$string['clinvalidkeyorsecret'] = 'Invalid key or secret';
$string['clfailtosavedata'] = 'Не удалось сохранить данные';
$string['clplagiarised'] = 'Степень сходства';
$string['clopenreport'] = 'Click to open plagkh report';
$string['clscoursesettings'] = 'Настройки плагиата';
$string['clupdateerror'] = 'Ошибка при попытке обновления записей в базе данных';
$string['clinserterror'] = 'Ошибка при попытке вставки записей в базу данных';
$string['clsendqueuedsubmissions'] = "Плагин проверки на плагиат - обработка ожидающих файлов";
$string['clsendresubmissionsfiles'] = "Плагин проверки на плагиат - обработка повторно отправленных результатов";
$string['clsendrequestqueue'] = "Плагин проверки на плагиат - обработка повторной попытки запросов в очереди";
$string['clupserteulausers'] = "Плагин проверки на плагиат -обработка пользователей, принявших Пользовательское соглашение";
$string['clupdatereportscores'] = "Плагин проверки на плагиат - обработка обновления степени сходства проверки на плагиат";
$string['cldraftsubmit'] = "Отправлять файлы только после нажатия студентами кнопки отправки";
$string['cldraftsubmit_help'] = "Этот параметр доступен только при включенной опции 'Требовать от студентов нажатия кнопки отправки'";
$string['clreportgenspeed'] = 'When to generate report?';
$string['clgenereportimmediately'] = 'Generate reports immediately';
$string['clgenereportonduedate'] = 'Generate reports on due date';
$string['cltaskfailedconnecting'] = 'Не удается установить соединение с сервером проверки на плагиат. Ошибка: {$a}';
$string['clapisubmissionerror'] = 'Сервер проверки на плагиат вернул ошибку при попытке отправки файла для проверки - ';
$string['clcheatingdetected'] = 'Cheating detected, Open report to learn more';
$string['clcheatingdetectedtxt'] = 'Обнаружено списывание';
$string['clreportpagetitle'] = 'plagkh report';
$string['clscansettingspagebtntxt'] = 'Изменить настройки сканирования';
$string['clmodulescansettingstxt'] = "Изменить настройки сканирования";
$string['cldisablesettingstooltip'] = "Выполняется синхронизация данных...";
$string['clopenfullscreen'] = 'Открыть в полноэкранном режиме';
$string['cllogsheading'] = 'Логи';
$string['clpoweredbyplagKh'] = 'Работает на локальном сервере проверки на плагиат';
$string['clplagiarisefailed'] = 'Ошибка';
$string['clplagiarisescanning'] = 'Поиск плагиата...';
$string['clplagiarisequeued'] = 'Запланировано сканирование на плагиат в {$a}';
$string['cldisabledformodule'] = 'Плагин проверки на плагиат отключен для этого модуля.';
$string['clnopageaccess'] = 'У вас нет доступа к этой странице.';
$string['privacy:metadata:core_files'] = 'Плагин проверки на плагиат хранит файлы, загруженные в Moodle для формирования проверки на плагиат.';
$string['privacy:metadata:plagiarism_plagKh_files'] = 'Информация, связывающая отправку Moodle с отправкой на сервер проверки на плагиат.';
$string['privacy:metadata:plagiarism_plagKh_files:userid'] = 'Идентификатор пользователя, являющегося владельцем отправки.';
$string['privacy:metadata:plagiarism_plagKh_files:submitter'] = 'Идентификатор пользователя, сделавшего отправку.';
$string['privacy:metadata:plagiarism_plagKh_files:similarityscore'] = 'Степень сходства отправки.';
$string['privacy:metadata:plagiarism_plagKh_files:lastmodified'] = 'Временная метка, указывающая, когда пользователь последний раз модифицировал свою отправку.';
$string['privacy:metadata:plagiarism_plagKh_client'] = 'Необходимо получить некоторые данные о пользователе.';
$string['privacy:metadata:plagiarism_plagKh_client:module_id'] = 'Идентификатор модуля, отправленный для идентификации.';
$string['privacy:metadata:plagiarism_plagKh_client:module_name'] = 'Название модуля, отправленное для идентификации.';
$string['privacy:metadata:plagiarism_plagKh_client:module_type'] = 'Тип модуля, отправленный для идентификации.';
$string['privacy:metadata:plagiarism_plagKh_client:module_creationtime'] = 'Время создания модуля, отправленное для идентификации.';
$string['privacy:metadata:plagiarism_plagKh_client:submittion_userId'] = 'Идентификатор пользователя отправки, отправленный для идентификации.';
$string['privacy:metadata:plagiarism_plagKh_client:submittion_name'] = 'Имя, отправленное для идентификации.';
$string['privacy:metadata:plagiarism_plagKh_client:submittion_type'] = 'Тип отправки, отправленный в plagkh для идентификации.';
$string['privacy:metadata:plagiarism_plagKh_client:submittion_content'] = 'Содержание отправки, отправленное в plagkh для обработки сканирования.';
