<?php
DEFINE('APPLICATION_NAME', 'ТелeCпутник');

DEFINE('CELL_PHONE_FORMAT', '/^\+375(25|29|33|44)\d{7}$/');
DEFINE('HOME_PHONE_FORMAT', '/^\+37516(2|3)\d{6}$/');

DEFINE('MONTH_FORMAT', 'm.Y');
DEFINE('DATE_FORMAT', 'd.m.Y');
DEFINE('DATETIME_FORMAT', 'd.m.Y H:i:s');
DEFINE('TIME_FORMAT', 'H:i:s');
DEFINE('SHORT_TIME_FORMAT', 'H:i');

DEFINE('CURRENCY_PREFIX', '');
DEFINE('CURRENCY_SUFFIX', ' p.');
DEFINE('CURRENCY_DECIMALS', '2');

DEFINE('NUMBER_DEC_POINT', ',');
DEFINE('NUMBER_THOUSANDS_SEP', ' ');

DEFINE('ERROR_FORMAT', 'неверный формат');
DEFINE('ERROR_BLANK', 'не может быть пустым');
DEFINE('ERROR_EXIST', 'уже существует');
DEFINE('ERROR_NOEXIST', 'не существует');
DEFINE('ERROR_NUMBER', 'не число');
DEFINE('ERROR_PAST', 'не может быть в прошлом');
DEFINE('ERROR_FUTURE', 'не может быть в будущем');
DEFINE('ERROR_BIG', 'слишком большое');
DEFINE('ERROR_LOW', 'слишком маленькое');

DEFINE('REQUIRED_FIELD', 'Обязательно для заполнения');
DEFINE('TABLE_NO_DATA', 'Нет данных для отображения');

DEFINE('ERROR_OLD_PASSWORD', 'не совпадает');
DEFINE('ERROR_NEW_PASSWORD', 'слишком короткий');
DEFINE('ERROR_PASSWORD_CONFIRM', 'не совпадает');

$SHORT_MONTHS = array('янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек');

date_default_timezone_set('Europe/Minsk');
setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251');
?>