"""
Two independent implementations of Rule 5/Rule 4 ("Contains forbidden HTML tags").

regex_has_tag   -- the implementation currently shipping in engine/testing.py/Comments.py
parser_has_tag  -- a second opinion built on Python's html.parser, which actually
                   tokenizes markup instead of pattern-matching characters.

Nothing here is wired into the real rule set yet -- this module is purely for
running the two side by side and finding out where they disagree.
"""

import re
from html.parser import HTMLParser

# Exactly the regex used by Rule 5 (comments) / Rule 4 (excerpts).
HTML_TAG_REGEX = re.compile(r"<\s*/?\s*[a-zA-Z][\w-]*[^>]*>")


def regex_has_tag(text: str) -> bool:
    return bool(HTML_TAG_REGEX.search(text))


class _TagDetector(HTMLParser):
    """Records whether the parser ever recognized a start/end/self-closing tag."""

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.found_tag = False
        self.tags_seen = []

    def handle_starttag(self, tag, attrs):
        self.found_tag = True
        self.tags_seen.append(tag)

    def handle_startendtag(self, tag, attrs):
        self.found_tag = True
        self.tags_seen.append(tag)

    def handle_endtag(self, tag):
        self.found_tag = True
        self.tags_seen.append(tag)


def parser_has_tag(text: str) -> bool:
    """True if html.parser recognizes at least one real start/end tag in `text`."""
    detector = _TagDetector()
    try:
        detector.feed(text)
        detector.close()
    except Exception:
        # html.parser is very lenient and rarely raises, but guard anyway --
        # a crash here is itself a finding, not something to hide.
        return detector.found_tag
    return detector.found_tag


def has_forbidden_html_tag(text: str) -> bool:
    """The decided-upon replacement for Rule 5/Rule 4's regex.

    Source of truth is the parser: it's spec-accurate about what a real
    browser would treat as a tag, so it doesn't false-positive on ordinary
    text that merely contains '<' and '>' (comparisons, generics, spaced-out
    "< script >" text that no renderer will ever execute as-is).

    One narrow patch: html.parser treats "<![CDATA[...]]>" as a single
    declaration and swallows anything nested inside it, including a real
    tag. A browser outside of foreign content (SVG/MathML) does not honor
    CDATA sections in HTML and would expose that nested tag. Rather than
    fall back to the (noisier) regex everywhere, only consult it when a
    CDATA-looking sequence is present.
    """
    if parser_has_tag(text):
        return True
    if "<![cdata[" in text.lower():
        return regex_has_tag(text)
    return False
