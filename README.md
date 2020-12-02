# Composer run parallel

This composer plugin allows you to run the tasks inside your composer file in parallel.
No more waiting on one specific task!

## Installation

```bash
composer require --dev veewee/composer-run-parallel
```

or globally ...

```bash
composer require --global veewee/composer-run-parallel
```

## Examples

```json
{
  "scripts": {
    "php1": "@php -r 'sleep(3); echo \"1\";'",
    "php2": "@php -r 'echo \"2\";'"
  }
}
```

Following command will result in parallel execution of both php1 and php2:

```bash
composer run parallel php1 php2
```

You can even create a shortcut script for the parallel function like this:

```json
{
  "scripts": {
    "php1": "@php -r 'sleep(3); echo \"1\";'",
    "php2": "@php -r 'echo \"2\";'",
    "php1And2": "@parallel php1 php2"
  }
}
```

```bash
composer run php1And2
```

What if a task fails?

```json
{
  "scripts": {
    "php1": "@php -r 'sleep(3); echo \"1\";'",
    "phpfail": "@php -r 'throw new Exception(\"FAIL\");'"
  }
}
```

All tasks will be executed and if one fails, the parallel task will fail as well:

```bash
composer run parallel php1 phpfail

Succesfully ran:
php1

Failed running:
phpfail

Not all tasks could be executed succesfully!
```

*Note*: You can also use the shorthand command:

```bash
composer parallel php1 phpfail
```

Pretty cool right?
What If I told you there is even more?!

```json
{
  "scripts": {
    "wicked": [
      "@parallel vendor php2",
      "@php1"
    ]
  }
}
```

You can even mix parallel tasks with serial tasks and even nest multiple parallel tasks at the same time.
This way you can create flows like:

- do some checks first
- next run some stuff in parallel
- finish up with some blocking cleanup task if everything was ok

