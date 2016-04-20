<?php
/**
 * Sample language
 */
return array(

	// Index method
	'members_title' => 'Пользователи',
	'firstName' => 'Имя',
    'lastName' => 'Фамилия',
	'password' => 'Пароль',
	'confirm_password' => 'Подтверждение пароля',
    'userName' => 'Имя пользователя',
    'userName_characters_error' => 'Имя пользователя должно содержать только буквы латиницы и числа, а также начинаться с букв',
    'userName_length_error' => 'Имя пользователя должно быть длиной от 5 до 20 символов',
	'firstName_length_error' => 'Имя должно быть длиной от 2 до 20 символов',
    'lastName_length_error' => 'Фамилия должна быть длиной от 2 до 20 символов',
	'enter_valid_email_error' => 'Введите правильный почтовый адрес',
	'email_taken_error' => 'Почтовый адрес занят',
	'password_short_error' => 'Пароль слишком короткий',
	'passwords_notmatch_error' => 'Пароли не совпадают',
    'tou_accept_error' => 'Вы должны принять Условия Использования',
    'sof_accept_error' => 'Вы должны принять Утверждение Веры',
    'accept_btn' => 'Принять',
    'deny_btn' => 'Отмена',
    'tou' => 'Условия использования',
    'sof' => 'Утверждение веры',
    'welcome_title' => 'Добро пожаловать',
    'translator' => 'Переводчик',
    'checker' => 'Проверяющий',
    'trans_checkr' => 'Переводчик/Проверяющий',
    'captcha_wrong' => 'Капча решена не верно',
    'userType_wrong_error' => 'Тип пользователя не верен',
    'registration_success_message' => 'Регистрация прошла успешно! Проверьте почту для активации учетной записи.',
    'success' => 'Успешно',

	// Activate method
	'activation_link_message' => '<h3>Спасибо за регистрацию!</h3>'."\n".' Чтобы активировать учетную запись перейдите по этой ссылке. <a href="{0}">{1}</a>',
	'no_account_error' => 'Учетная запись не существует или код недействителен',
	'account_activated_error' => 'Account has already been activated',
	'account_activated_success' => 'Учетная запись активирована. Теперь вы можете <a href="{0}">Войти</a>',
	'invalid_link_error' => 'Неверная ссылка активации',
	'activate_account_title' => 'Активация учетной записи',

	// Login method
	'wrong_credentials_error' => 'Неверный адрес/имя пользователя или пароль, или учетная запись не активирована',
	'login' => 'Войти',
	'signup' => 'Регистрация',
	'logout' => 'Выйти',
	'login_message' => 'Регистрация',
	'already_member' => 'Уже являетесь пользователем?',
    'login_title' => 'Авторизация',
    'forgot_password' => 'Забыли пароль?',
    'update_table_error' => 'Ошибка записи в базу данных. Пожалуйста, попробуйте снова.',

    // Passwordreset method
    'passwordreset_title' => 'Сброс пароля',
	'continue' => 'Продолжить',
	'enter_email' => 'Введите Email',
	'passwordreset_link_message' => 'Чтобы сбросить пароль, перейдите по ссылке. <a href="{0}">{1}</a>',
	'pwresettoken_send_success' => 'Вам было отправлено письмо с инструкциями по сбросу пароля.',
	'password_reset_success' => 'Ваш пароль был успешно изменен. Теперь вы можете войти, используя новый пароль <a href="{0}">Войти</a>',
	'token_expired_error' => 'Код сброса пароля просрочен',
);
