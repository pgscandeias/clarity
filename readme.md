# Clarity

Just fooling around with PHP, ending up with another team chat app.


## About

### Architecture

Custom made micro framework which follows the pattern of `$app->{method}($params)`.


### Security

Users can log in without passwords. Instead, they're sent an access url by email every time they wish to start a new session. That url contains an access token wich, as long as the connection is TLS protected, doesn't leak. This is theoretically more secure and fail proof than password protection.


### Testing and code quality

Being an experiment, there's little in the way of testing or refactoring. So the code is far from my production standards.


## Requirements

* PHP 5.3
* MySQL 5+
* Apache2 with mod_rewrite
* A valid SSL certificate in production
