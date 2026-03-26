---
applyTo: "**/*.css"
---

# CSS Coding Style

## Braces — Expand Style
Opening braces go on their **own line**. Each declaration on its own line. Closing brace on its own line.

```css
/* Correct */
.selector
{
	property: value;
	property: value;
}

/* Wrong */
.selector {
	property: value;
}
```

## Indentation
- Use **tabs**, never spaces.

## Selectors
- Each selector in a comma-separated group on its own line.

```css
.foo,
.bar,
.baz
{
	color: red;
}
```

## Declarations
- One declaration per line.
- Always end declarations with a semicolon.
- Space after `:` — `color: red` not `color:red`.

## Comments
- Section comments use `/* Comment */` style.
- Inline comments go above the line they describe, not at end of line.
