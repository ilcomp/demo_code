<?php
// Heading
$_['heading_title']                  = 'Настройки отправки Email (почты)';

// Text
$_['text_stores']                    = 'Магазины';
$_['text_success']                   = 'Настройки успешно изменены!';
$_['text_edit']                      = 'Редактирование';
$_['text_error']                     = 'Ошибки';
$_['text_error']                     = 'Ошибки';
$_['text_mail']                      = 'Mail';
$_['text_smtp']                      = 'SMTP';

// Entry
$_['entry_email']                    = 'E-Mail для отправки писем';
$_['entry_mail_engine']              = 'Почтовый протокол';
$_['entry_mail_parameter']           = 'Параметры функции mail';
$_['entry_mail_smtp_hostname']       = 'SMTP Имя сервера';
$_['entry_mail_smtp_username']       = 'SMTP Логин';
$_['entry_mail_smtp_password']       = 'SMTP Пароль';
$_['entry_mail_smtp_port']           = 'SMTP Порт';
$_['entry_mail_smtp_timeout']        = 'SMTP Таймаут';
$_['entry_mail_alert']               = 'Получать оповещения:';
$_['entry_mail_alert_email']         = 'Дополнительные адреса оповещения';

// Help
$_['help_mail_engine']               = 'Выбирайте \'Mail\', и только в случае, когда этот способ не работает, то &mdash; SMTP.';
$_['help_mail_parameter']            = 'ОСТОРОЖНО. Не заполняйте это поле, если не знаете, для чего оно. Когда используется \'Mail\', здесь могут быть указаны дополнительные параметры для sendmail (напр. \'-femail@storeaddress.com\').';
$_['help_mail_smtp_hostname']        = 'Добавьте \'tls://\' префикс если требуется защищенное соединение. (пример \'tls://smtp.gmail.com\').';
$_['help_mail_smtp_password']        = 'Для Gmail может потребоваться настройка приложения и пароля: <a href="https://security.google.com/settings/security/apppasswords" target="_blank">https://security.google.com/settings/security/apppasswords</a>';
$_['help_mail_alert']                = 'Выберите события от которых вы хотели бы получать оповещения.';
$_['help_mail_alert_email']          = 'Перечислите дополнительные e-mail адреса магазина, для получения оповещений на них. (разделитель запятая).';

// Error
$_['error_warning']                  = 'Ошибка! Проверьте форму на наличие ошибок!';
$_['error_permission']               = 'У Вас нет прав для изменения настроек!';
$_['error_email']                    = 'E-Mail адрес введен неверно!';