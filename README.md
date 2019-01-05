# Toei [![CircleCI](https://circleci.com/gh/g737a6b/php-toei.svg?style=svg)](https://circleci.com/gh/g737a6b/php-toei)

PHP library to integrate scattered events in RDB.

## 1. Examples of use

### 1-1. RDB (precondition)

```sql
-- Table "users"
CREATE TABLE `users` (`id` INT, `name` TEXT, `created` DATETIME, `deleted` DATETIME);
INSERT INTO `users` (`id`, `name`, `created`, `deleted`)
VALUES (1, 'Suzuki', '2017-01-01 12:04:11', NULL),
	(2, 'Tanaka', '2017-01-21 09:57:48', '2017-03-20 18:03:30'),
	(3, 'Yoshida', '2017-02-04 20:47:25', NULL);

-- Table "messages"
CREATE TABLE `messages` (`sender` INT, `receiver` INT, `body` TEXT, `created` DATETIME);
INSERT INTO `messages` (`sender`, `receiver`, `body`, `created`)
VALUES (1, 2, 'Hi!', '2017-01-21 12:01:44'),
	(2, 3, 'Hi!', '2017-02-04 21:54:17'),
	(1, 3, 'Hi!', '2017-02-05 12:03:01'),
	(2, 1, 'Bye!', '2017-03-20 17:54:46'),
	(2, 3, 'Bye!', '2017-03-20 17:56:23');
```

### 1-2. config.json

```json
{
	"register": {
		"table": "users",
		"identifyBy": "id",
		"sortBy": "created"
	},
	"withdraw": {
		"table": "users",
		"identifyBy": "id",
		"sortBy": "deleted",
		"condition": "created > '2000-01-01 00:00:00'"
	},
	"send_message": {
		"table": "messages",
		"identifyBy": "sender",
		"sortBy": "created"
	},
	"recieve_message": {
		"table": "messages",
		"identifyBy": "receiver",
		"sortBy": "created"
	}
}
```

### 1-3. PHP

```php
$config = json_decode(file_get_contents("config.json"));
$Toei = new Toei\Toei($PDO, $config);
$Toei->setId(2);
$result = $Toei->project(true);

// array(6) {
//   [0]=>
//   array(3) {
//     ["action"]=>
//     string(8) "register"
//     ["id"]=>
//     string(1) "2"
//     ["time"]=>
//     string(19) "2017-01-21 09:57:48"
//   }
//   [1]=>
//   array(3) {
//     ["action"]=>
//     string(15) "recieve_message"
//     ["id"]=>
//     string(1) "2"
//     ["time"]=>
//     string(19) "2017-01-21 12:01:44"
//   }
//   [2]=>
//   array(3) {
//     ["action"]=>
//     string(12) "send_message"
//     ["id"]=>
//     string(1) "2"
//     ["time"]=>
//     string(19) "2017-02-04 21:54:17"
//   }
//   [3]=>
//   array(3) {
//     ["action"]=>
//     string(12) "send_message"
//     ["id"]=>
//     string(1) "2"
//     ["time"]=>
//     string(19) "2017-03-20 17:54:46"
//   }
//   [4]=>
//   array(3) {
//     ["action"]=>
//     string(12) "send_message"
//     ["id"]=>
//     string(1) "2"
//     ["time"]=>
//     string(19) "2017-03-20 17:56:23"
//   }
//   [5]=>
//   array(3) {
//     ["action"]=>
//     string(8) "withdraw"
//     ["id"]=>
//     string(1) "2"
//     ["time"]=>
//     string(19) "2017-03-20 18:03:30"
//   }
// }
```

## 2. Installation

### 2-1. Composer

Add a dependency to your project's `composer.json` file.

```json
{
	"require": {
		"g737a6b/toei": "*"
	}
}
```

## 3. License

[The MIT License](http://opensource.org/licenses/MIT)

Copyright (c) 2018 [Hiroyuki Suzuki](https://mofg.net)
