#!/usr/bin/env python3
"""Minify the plugin's stylesheets in place for a release build.

Deliberately conservative: strips comments, collapses whitespace and removes
the spaces around braces, semicolons and commas. Strings and url(...) values
are copied through untouched, and spaces inside values (calc, selectors,
shorthand) are kept, so the result parses identically to the source.

Usage: minify_css.py <file.css> [<file.css> ...]
"""
import re
import sys


def minify(css: str) -> str:
    out = []
    i, n = 0, len(css)
    while i < n:
        c = css[i]
        # comments
        if css.startswith("/*", i):
            end = css.find("*/", i + 2)
            i = n if end == -1 else end + 2
            out.append(" ")
            continue
        # strings, copied verbatim
        if c in "\"'":
            j = i + 1
            while j < n and css[j] != c:
                j += 2 if css[j] == "\\" else 1
            out.append(css[i : j + 1])
            i = j + 1
            continue
        # url(...) with an unquoted value, copied verbatim
        if css.startswith("url(", i) and i + 4 < n and css[i + 4] not in "\"'":
            j = css.find(")", i)
            j = n - 1 if j == -1 else j
            out.append(css[i : j + 1])
            i = j + 1
            continue
        out.append(c)
        i += 1

    # Work on the parts between protected tokens only.
    protected = re.compile(r"""("(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|url\([^"')][^)]*\))""")
    parts = protected.split("".join(out))
    for k in range(0, len(parts), 2):
        t = re.sub(r"\s+", " ", parts[k])
        t = re.sub(r" ?([{};]) ?", r"\1", t)
        t = re.sub(r", ", ",", t)
        t = t.replace(";}", "}")
        parts[k] = t
    return "".join(parts).strip() + "\n"


def main() -> int:
    if len(sys.argv) < 2:
        print(__doc__)
        return 2
    for path in sys.argv[1:]:
        with open(path, encoding="utf-8") as fh:
            src = fh.read()
        result = minify(src)
        if result.count("{") != src_braces(src)[0] or result.count("}") != src_braces(src)[1]:
            print(f"error: brace count changed while minifying {path}", file=sys.stderr)
            return 1
        with open(path, "w", encoding="utf-8") as fh:
            fh.write(result)
        print(f"{path}: {len(src.encode())} -> {len(result.encode())} bytes")
    return 0


def src_braces(css: str):
    """Brace counts of the source with comments removed (strings never hold braces here)."""
    stripped = re.sub(r"/\*.*?\*/", "", css, flags=re.S)
    return stripped.count("{"), stripped.count("}")


if __name__ == "__main__":
    sys.exit(main())
