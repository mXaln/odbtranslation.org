<?php
/**
 * Sample language
 */
return array(

	// Index method
	'members_title' => 'Members',
	'firstName' => 'First name',
    'lastName' => 'Last name',
	'password' => 'Password',
	'confirm_password' => 'Confirm Password',
    'userName' => 'User name',
    'userName_characters_error' => 'User name should contain only latin letters and numbers and start with letters',
    'userName_length_error' => 'User name should be of length between 5 and 20',
	'firstName_length_error' => 'First name should be of length between 2 and 20',
    'lastName_length_error' => 'Last name should be of length between 2 and 20',
	'enter_valid_email_error' => 'Please enter a valid email address',
	'email_taken_error' => 'Email already taken',
	'password_short_error' => 'Password is too short',
	'passwords_notmatch_error' => 'Passwords do not match',
    'tou_accept_error' => 'You must accept Terms of Use',
    'sof_accept_error' => 'You must accept Statement of Faith',
    'accept_btn' => 'Accept',
    'deny_btn' => 'Cancel',
    'tou' => 'Terms of use',
    'sof' => 'Statement of faith',
	'welcome_title' => 'Welcome',
	'translator' => 'Translator',
	'checker' => 'Checker',
	'trans_checkr' => 'Translator/Checker',
    'captcha_wrong' => 'Captcha wasn\'t solved correctly',
    'userType_wrong_error' => 'Type of user is invalid',
	'registration_success_message' => 'Registration Successfull! Please check your email to complete registration.',
	'success' => 'Success',

	// Activate method
	'activation_link_message' => 'Thank you for registering. To activate your account please click on this link. <a href="{0}">{1}</a>',
	'no_account_error' => 'No such account or token is not valid',
	'account_activated_error' => 'Account has already been activated',
	'account_activated_success' => 'Your account is now active you may now <a href="{0}">Login</a>',
	'invalid_link_error' => 'Invalid link provided',
	'activate_account_title' => 'Activate Account',

	// Login method
	'wrong_credentials_error' => 'Wrong email/user name or password or account has not been activated yet',
	'login' => 'Sign in',
	'signup' => 'Sign up',
	'logout' => 'Logout',
	'login_message' => 'Sign Up',
	'already_member' => 'Already a member?',
	'login_title' => 'Authorization',
    'forgot_password' => 'Forgot password?',
    'update_table_error' => 'Error while updating database. Please try again.',

    // Passwordreset method
    'passwordreset_title' => 'Password reset',
	'continue' => 'Continue',
	'enter_email' => 'Enter Email',
	'passwordreset_link_message' => 'To reset your password please click on this link. <a href="{0}">{1}</a>',
	'pwresettoken_send_success' => 'We\'ve sent you an email with instructions how to reset your password.',
	'password_reset_success' => 'Your password has been changed successfully. You may now login using your new password <a href="{0}">Login</a>',
	'token_expired_error' => 'Reset token has expired',
);
