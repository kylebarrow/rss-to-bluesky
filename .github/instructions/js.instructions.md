---
applyTo: "**/*.{js,mjs,cjs}"
---

# JavaScript Coding Style

All rules below are enforced by ESLint (`eslint.config.mjs`) on file save. Generate code that already matches these rules.

## Indentation
- Use **tabs**, never spaces.

## Braces — Allman Style
Opening braces go on their **own line**. Single-line blocks are allowed.

```js
if (condition)
{
	doSomething();
}
else
{
	doSomethingElse();
}
```

## Quotes
- **Single quotes** for all strings: `'hello'`.
- Template literals are allowed when needed.
- JSX uses single quotes.

## Semicolons
- Always required. Never omit.

## Variables
- Always use `const` or `let`, never `var`.
- Prefer `const` for anything that is not reassigned.
- Declare variables at the top of their scope (`vars-on-top`).
- One declaration per line.
- Always initialise variables on declaration (`init-declarations`).

## Functions
- Use **function declarations**, not function expressions assigned to variables.
  ```js
  // Correct
  function doThing() {}

  // Wrong
  const doThing = function() {};
  ```
- No space before `(` in function declarations or calls: `function foo()`, `foo()`.
- Arrow functions always require parentheses around parameters: `(x) => x`.
- Arrow function body: use braces and explicit return unless trivially simple. (`arrow-body-style` enforced.)

## Naming
- **`camelCase`** for variables, functions, and object properties.
- **`PascalCase`** for classes (`new-cap`).

## Spacing
- Space after keywords: `if (`, `for (`, `while (`, `switch (`, `catch (`.
- No space before `(` in function calls or declarations.
- Spaces around infix operators: `a + b`, `a === b`.
- Space before blocks: `} else {` → N/A with Allman, but blocks always get a space before `{` when on the same line.
- No trailing spaces on any line.
- No multiple consecutive empty lines.
- No padding lines inside blocks.
- No whitespace before property access: `obj.prop` not `obj .prop`.

## Commas
- Comma-first style is **not** used — commas at end of line.
- No trailing commas.
- Spaces after commas, not before.

## Arrays
- No spaces inside brackets: `[1, 2, 3]`.
- Items go on their own lines when there are 4 or more, or when any item is multiline.
  ```js
  // 3 items — can be one line
  const a = [1, 2, 3];

  // 4+ items — one per line
  const b = [
  	'alpha',
  	'beta',
  	'gamma',
  	'delta'
  ];
  ```

## Objects
- No spaces inside `{}`: `{key: value}` unless wrapping.
- All properties on one line is allowed; otherwise one property per line.
- Quote object keys only when all keys in the object need quoting (`consistent-as-needed`).
- Newline before `}` when wrapping.

## Chaining
- Each chained call on its own line (`newline-per-chained-call`).
- The `.` goes at the start of the continuation line (`dot-location: property`).
  ```js
  promise
  	.then(handleResult)
  	.catch(handleError);
  ```

## Operators
- Always use `===` and `!==`, never `==` or `!=`.
- No yoda conditions: `value === 'foo'` not `'foo' === value`.
- Use `**` instead of `Math.pow`.
- Use logical assignment operators (`||=`, `&&=`, `??=`) where applicable.
- Use `operator-assignment` shorthand (`+=`, `-=`, etc.).
- No mixed operators without explicit grouping (parentheses required).
- Ternaries must never be multiline — if you need multiline, use `if/else`.

## Comments
- Only above-line comments, not inline end-of-line comments (`line-comment-position: above`).
- Spaced comment markers: `// text` not `//text`.
- No warning/fixme/todo comments left in production code (`no-warning-comments`).

## Classes
- Maximum 1 class per file.
- Class methods must use `this`, or be made static.
- Blank line between class members.
- No unused private class members.

## Error Handling
- Always use `Error` objects (or subclasses) with `throw`, never plain strings or literals.
- Prefer `Promise.reject(new Error(...))`.
- `await` only inside `async` functions. No `await` inside loops.
- Every `async` function must have an `await`.

## Miscellaneous Hard Rules
- No `console.*` calls.
- No `debugger`.
- No `eval` or implied eval.
- No `alert`.
- No bitwise operators.
- No `continue`.
- No `new` for side effects only (result must be used).
- No nested ternaries.
- No unneeded ternaries — use a simpler expression.
- No parameter reassignment.
- No variable shadowing.
- No use before define.
- No unused variables or imports.
- No duplicate imports — consolidate into one import statement.
- No `for...in` without `hasOwnProperty` guard (`guard-for-in`); prefer `Object.hasOwn()`.
- No magic numbers (disabled — allowed).
- Prefer `Object.hasOwn()` over `object.hasOwnProperty()`.
- Prefer spread `{...obj}` over `Object.assign({}, obj)`.
- Prefer rest params over `arguments`.
- Prefer `Number.isNaN()` over `isNaN()`.

## Globals
- Available globals: browser (`window`, `document`, etc.), Node.js, and jQuery (`$`, `jQuery`).
- Do not declare variables named `undefined`.

## File Structure
- ES module syntax (`import`/`export`), `sourceType: module`.
- ECMAScript version: latest.
- Files end with a newline.
- Max file length: 1000 lines (warning).
- Max function length: 400 non-blank, non-comment lines (warning).
- Max statements per function: 30 (warning, top-level functions excluded).
- Max nesting depth: 4.
- Max params per function: 3.
- Max nested callbacks: 10.
