---
applyTo: "**/*.php"
---

# PHP Coding Style

All rules below are enforced by the PHP Tools formatter on file save. Generate code that already matches these rules.

## Indentation
- Use **tabs**, never spaces.

## Braces — Allman Style
Opening braces always go on their **own line** for all constructs: classes, functions, methods, `if`, `else`, `elseif`, `catch`, `finally`, `foreach`, `for`, `while`, `switch`, anonymous classes, lambdas, namespaces.

`else`, `catch`, and `finally` each start on a new line after the closing `}`.

```php
if ($condition)
{
	doSomething();
}
else
{
	doSomethingElse();
}

try
{
	riskyCall();
}
catch (Exception $e)
{
	handleError($e);
}
finally
{
	cleanup();
}
```

## Spacing
- **Space before `(` in control structures** — `if (`, `foreach (`, `while (`, `for (`, `switch (`, `catch (`.
- **No space before `(` in function/method calls** — `foo()`, `$this->bar()`.
- **No space before `(` in function/method declarations** — `function foo()`.
- **No space before `(` in arrow functions** — `fn($x) => $x`.
- **No spaces inside parentheses** — `if ($x)` not `if ( $x )`.
- **Space after type casts** — `(int) $value`, `(string) $value`.
- **No space after unary `!`** — `!$condition` not `! $condition`.
- **Spaces around concatenation operator** — `'foo' . 'bar'`.
- **No space before `:` in return types** — `function foo(): string`.
- **No space before `:` in alternative control structure syntax**.

## Wrapping
Each item on its own line when wrapping. Opening delimiter stays on the same line; closing delimiter goes on its own line.

**Arrays:**
```php
$items = [
	'key1' => 'value1',
	'key2' => 'value2',
];
```
- Align `=>` operators vertically within the same array.
- No trailing comma after the last element.

**Function call arguments:**
```php
someFunction(
	$argument1,
	$argument2,
	$argument3
);
```
- No trailing comma after the last argument.

**Function/method declaration parameters:**
```php
public function myMethod(
	string $param1,
	int $param2,
	bool $param3
): void
{
}
```
- No trailing comma after the last parameter.
- Closing `)` and opening `{` are on separate lines (not kept on one line).
- Empty function bodies are not compacted — always use the full brace block.

**Chained method calls** — each call on its own line; semicolon stays on the last line:
```php
$result = $object->firstCall()
	->secondCall()
	->thirdCall();
```

**`implements` list** — each interface on its own line, starting after a newline:
```php
class Foo
	implements Bar,
	           Baz
{
}
```

**`for` / `if` / `switch` / `while` conditions** — wrapped across lines when long, with newline after `(` and before `)`.

## Alignment
Align consecutive assignments, properties, constants, enum cases, and match arm bodies vertically:

```php
protected static $instance        = null;
private          $email_processor = null;

const FOO   = 1;
const BAR   = 2;
const BAZ   = 10;
```

## Constants & Booleans
- `true`, `false`, `null` — always lowercase.

## Blank Lines
- 1 blank line after `<?php`.
- 1 blank line before each class, function, and method definition.
- No blank lines after opening class/function/method body.
- No blank lines after closing class/function/method body.
- 1 blank line before `use` statements; 1 blank line between use-type groups.
- Maximum 2 consecutive blank lines anywhere.
- Every file ends with a newline.

## Short Constructs
- Short classes (no body lines) may stay on one line.
- Short single-statement control structures may stay on one line.
- Functions always use the full block form — never on one line.

## Arrays
- Use short array syntax `[]`, never `array()`.

## Naming Conventions
- **Classes**: `PascalCase_With_Underscores` — e.g., `Event_Lottery_Plugin`
- **Methods & functions**: `snake_case` — e.g., `db_install`, `load_plugin`
- **Constants**: `UPPER_SNAKE_CASE` — e.g., `EVENT_LOTTERY_VERSION`

## Strings
- Prefer single quotes for plain strings; use double quotes only when interpolation is needed.

## WordPress Patterns
- Guard class definitions with `!class_exists()`.
- Guard constant definitions with `!defined()`.
- Exit immediately if `ABSPATH` is not defined at the top of every file.
- Use `require_once` without parentheses: `require_once 'path/to/file.php';`

## PHPDoc
- Add PHPDoc blocks to all class methods with at minimum `@since`, `@param` (if applicable), and `@return` (if applicable).
