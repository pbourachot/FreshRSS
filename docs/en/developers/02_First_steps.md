# Environment configuration (Docker)

FreshRSS is built with PHP and uses a homemade framework, Minz. The dependencies are directly included in the source code, so you don’t need Composer.

There are various ways to configure your development environment. The easiest and most supported method is based on Docker, which is the solution documented below. If you already have a working PHP environment, you probably don’t need it.

We assume here that you use a GNU/Linux distribution, capable of running Docker. Otherwise, you’ll have to adapt the commands accordingly.

The commands that follow have to be executed in a console. They start by `$` when commands need to be executed as normal user, and by `#` when they need to be executed as root user. You don’t have to type these characters. A path may be indicated before these characters to help you identify where they need to be executed. For instance, `app$ echo 'Hello World'` indicates that you have to execute `echo` command in the `app/` directory.

First, you need to install [Docker](https://docs.docker.com/install/linux/docker-ce/ubuntu/).

Once you’re done, clone the repository with:

```sh
git clone https://github.com/FreshRSS/FreshRSS.git
cd FreshRSS
```

Note that, if you want to contribute, you have to fork the repository first and clone your fork instead of the "root" one. Adapt the commands in consequence.

Then, the only command you need to know is the following:

```sh
make start
```

This might take some time while Docker downloads the image. If your user isn’t in the `docker` group, you’ll need to prepend the command with `sudo`.

**You can now access FreshRSS at [http://localhost:8080](http://localhost:8080).** Just follow the install process and select the SQLite database.

You can stop the containers by typing <kbd>Control</kbd> + <kbd>c</kbd> or with the following command, in another terminal:

```sh
make stop
```

If you’re interested in the configuration, the `make` commands are defined in the [`Makefile`](/Makefile).

If you need to use a different tag image (default is `alpine`), you can set the `TAG` environment variable:

```sh
TAG=arm make start
```

You can find the full list of available tags [on the Docker hub](https://hub.docker.com/r/freshrss/freshrss/tags).

If you want to build the Docker image yourself, you can use the following command:

```sh
make build
# or
TAG=arm make build
```

The `TAG` variable can be anything (e.g. `local`). You can target a specific architecture by adding `-alpine` or `-arm` at the end of the tag (e.g. `local-arm`).

## Project architecture

- the PHP framework: [Minz](Minz/index.md)

## Extensions

If you want to create your own FreshRSS extension, take a look at the [extension documentation](03_Backend/05_Extensions.md).

## Coding style

If you want to contribute to the source code, it’s important to follow the project’s coding style. The actual code doesn’t always follow it throughout the project, but we should fix it every time an opportunity presents itself.

Contributions which don’t follow the coding style will be rejected as long as the coding style is not fixed.

## GitHub Actions

The code will be checked for every pull request commit on GitHub via [GitHub Actions](https://github.com/FreshRSS/FreshRSS/actions).
See the configuration file [`tests.yml`](../../../.github/workflows/tests.yml).

## Running fixes & tests

Tests can be run locally, e.g. by running `make test-all`, and several problems can be automatically fixed by running `make fix-all`.

```sh
make fix-all
make test-all
```

This requires `make` and `npm` in addition to the FreshRSS requirements. See below for the precise requirements for a few platforms.

### Debian / Ubuntu

> ℹ️ Also applies to [Microsoft Windows](https://docs.microsoft.com/windows/wsl/install-win10) thanks to [WSL](https://ubuntu.com/wsl).

Here are the dependencies that need to be manually installed prior to running the fixes & tests.

```sh
sudo apt update && sudo apt install --no-install-recommends -y make npm php-cli php-curl php-mbstring php-xml unzip wget
```

### Fedora / Red Hat

```sh
yum install -y git make npm php-cli php-curl php-mbstring php-xml php-pdo unzip wget
```

### Alpine Linux

```sh
apk add git make npm php-cli php-curl php-ctype php-dom php-mbstring php-openssl php-phar php-simplexml php-xml php-pdo php-tokenizer php-xmlwriter unzip wget
```

### Partial fixes & tests

- composer-based: `npm run fix && npm test` or see the [`scripts` section of `composer.json`](../../../composer.json) for individual tests or fixes such as `composer phpstan`
- npm-based: `npm run fix && npm test` or see the [`scripts` section of `package.json`](../../../package.json) for individual tests or fixes such as `npm run rtlcss`

### Tests summary

A short (not complete) summary:

#### PHP

- Syntax of `php` and `phtml` files is checked.
- translation files (`i18n`) are checked ([more information about i18n files](internationalization.html)).
- unit test (`tests`) are run by [PHPunit](https://phpunit.de/).
- Linter:
  - [PHP_Codesniffer (phpcs)](https://github.com/squizlabs/PHP_CodeSniffer)
  - [PHPstan](https://github.com/phpstan/phpstan)

### CSS

- Linter:
  - [PHP_Codesniffer (phpcs)](https://github.com/squizlabs/PHP_CodeSniffer)
  - via npm `.styleintrc.json`
  - check that RTL (right-to-left) CSS files match to standard CSS files

### JavaScript

- Linter:
  - via npm `.styleintrc.json` ([ECMAScript 2017](https://en.wikipedia.org/wiki/ECMAScript#8th_Edition_%E2%80%93_ECMAScript_2017))

### Markdown

- Linter:
  - via npm `.markdownlint.json`

## Spaces, tabs and other whitespace characters

### Indentation

Code indentation must use tabs.

### Alignment

Once the code has been correctly indented, it might be useful to align it for ease of reading. In that case, please use spaces.

```php
$result = a_function_with_a_really_long_name($param1, $param2,
                                             $param3, $param4);
```

### End of line

The newline character must be a line feed (LF), which is the default line ending on *NIX systems. This character must not follow other white space.

You can verify if there is any unintended white space at the end of line with the following Git command:

```sh
# command to check files before adding them in the Git index
git diff --check
# command to check files after adding them in the Git index
git diff --check --cached
```

### End of file

Every file must end by an empty line.

### Commas, dots and semi-columns

There should no space before those characters, but there should be one after.

### Operators

There should be a space before and after every operator.

```php
if ($a == 10) {
	// do something
}

echo $a ? 1 : 0;
```

### Parentheses

There should be no spaces in between brackets. There should be no spaces before the opening bracket, except if it’s after a keyword. There shouldn’t be any spaces after the closing bracket, except if it’s followed by a curly bracket.

```php
if ($a == 10) {
	// do something
}

if ((int)$a == 10) {
	// do something
}
```

### With chained functions

It happens most of the time in JavaScript files. When there are chained functions with closures and call-back functions, it’s hard to understand the code if not properly formatted. In those cases, we add a new indent level for the complete instruction and reset the indent for a new instruction on the same level.

```javascript
// First instruction
shortcut.add(shortcuts.mark_read, function () {
		//...
	}, {
		'disable_in_input': true
	});
// Second instruction
shortcut.add("shift+" + shortcuts.mark_read, function () {
		//...
	}, {
		'disable_in_input': true
	});
```

## Line length

Lines should strive to be shorter than 80 characters. However, this limit may be extended to 100 characters when strictly necessary.

With functions, parameters can be declared on multiple lines.

```php
function my_function($param_1, $param_2,
                     $param_3, $param_4) {
	// do something
}
```

## Naming

All code elements (functions, classes, methods and variables) must describe their usage succinctly.

### Functions and variables

Functions and variables must follow the "snake case" naming convention.

```php
// a function
function function_name() {
	// do something
}
// a variable
$variable_name;
```

### Methods

Methods must follow the "lower camel case" naming convention.

```php
private function methodName() {
	// do something
}
```

### Classes

Classes must follow the "upper camel case" naming convention.

```php
abstract class ClassName {}
```

## Encoding

Files must be encoded with the UTF-8 character set.

## PHP compatibility

Please ensure that your code works with the oldest PHP version officially supported by FreshRSS.

## Miscellaneous

### Operators on multiple lines

Operators must be at the end of the line if a condition is split over more than one line.

```php
if ($a == 10 ||
    $a == 20) {
	// do something
}
```

### End of PHP file

If the file contains only PHP code, the PHP closing tag must be omitted.

### Arrays

If an array declaration runs on more than one line, each element must be followed by a comma, including the last one.

```php
$variable = [
	"value 1",
	"value 2",
	"value 3",
];
```
