import json
import random
import sys

sys.path.insert(0, "tests/AiTesting")  # engine.py, testing.py, Comments.py live here

from html_compare import regex_has_tag, parser_has_tag

import Comments as comment_domain
import Testing as excerpt_domain


def load_corpus():
    """Collect every string we can throw at the two implementations."""
    corpus = []  # list of (source_label, text)

    # 1. Real dataset values
    with open("tests/AiTesting/Comments.json", encoding="utf-8") as f:
        for item in json.load(f):
            c = item.get("comment")
            if isinstance(c, str):
                corpus.append(("dataset:comment", c))

    with open("tests/AiTesting/Articles.json", encoding="utf-8") as f:
        for item in json.load(f):
            e = item.get("excerpt")
            if isinstance(e, str):
                corpus.append(("dataset:excerpt", e))

    # 2. Fuzz generators, many iterations, seeded for reproducibility
    random.seed(2024)
    for _ in range(3000):
        corpus.append(("fuzz:comment", comment_domain.generate_random_comment()))
    for _ in range(3000):
        corpus.append(("fuzz:excerpt", excerpt_domain.generate_random_excerpt()))

    # 3. Hand-crafted edge cases, organized by the category they're meant to probe
    handcrafted = {
        "well_formed_tags": [
            "<b>bold</b>",
            "<div class='x'>text</div>",
            "<img src=x onerror=alert(1)>",
            "<script>alert(1)</script>",
            "<BR>",
            "<h1>Heading</h1>",
        ],
        "self_closing": [
            "<br/>",
            "<br />",
            "<img src='a.png'/>",
        ],
        "malformed_no_close": [
            "<b",
            "<div class='x'",
            "text < b",
        ],
        "malformed_no_open_letter": [
            "< >",
            "<>",
            "< 1a>",
            "<1a>",
            "<-b>",
        ],
        "comparison_operators": [
            "x < y > z",
            "if a<b> then c",
            "3 < 5 and 10 > 2",
            "a < b",
            "vector<int> nums;",
            "list<Map<String, Integer>>",
        ],
        "html_comments": [
            "<!-- just a comment -->",
            "<!--[if IE]>trident<![endif]-->",
        ],
        "doctype_and_pi": [
            "<!DOCTYPE html>",
            "<?php echo 1; ?>",
            "<?xml version='1.0'?>",
        ],
        "namespaced_and_custom": [
            "<ns:tag>content</ns:tag>",
            "<my-custom-element>text</my-custom-element>",
            "<x-foo bar='1'>hi</x-foo>",
        ],
        "attribute_with_angle_in_quotes": [
            "<div title='1 < 2'>text</div>",
            '<a href="x?a=1&b=2" title="a>b">link</a>',
        ],
        "entities_not_tags": [
            "&lt;b&gt;bold&lt;/b&gt;",
            "5 &lt; 10 &gt; 2",
        ],
        "unicode_lookalikes": [
            "\uFF1Cb\uFF1E",       # fullwidth < b >
            "text \u2039tag\u203A more",  # single angle quotes, not real brackets
        ],
        "case_and_whitespace": [
            "< B >bold</ B >",
            "<  div  >x</  div  >",
            "<DiV>mixed case</DiV>",
        ],
        "nested_broken_brackets": [
            "<<b>>",
            "<< >>",
            "a<<<b>>>c",
        ],
        "script_style_raw_text": [
            "<script>if (1<2) { alert('x') }</script>",
            "<style>.a{color:red}</style>",
        ],
        "empty_and_trivial": [
            "",
            " ",
            "<",
            ">",
            "<>",
        ],
        "long_garbage": [
            "<" * 2000,
            ">" * 2000,
            "<" * 1000 + "b" + ">" * 1000,
        ],
        "plain_text_with_lt_gt": [
            "temperature < 5 degrees",
            "score > 90%",
            "1 < 2 < 3",
            "use the < and > symbols carefully",
        ],
        "cdata": [
            "<![CDATA[some <b>data</b>]]>",
        ],
        "attribute_only_no_text": [
            "<input type='text' value='<script>'>",
        ],
        "mismatched_quotes": [
            "<div class=\"a'>text</div>",
            "<div class='a\">text</div>",
        ],
    }

    for category, examples in handcrafted.items():
        for text in examples:
            corpus.append((f"handcrafted:{category}", text))

    return corpus


def main():
    corpus = load_corpus()
    print(f"Total inputs to compare: {len(corpus)}")

    disagreements = []  # (source, text, regex_result, parser_result)
    for source, text in corpus:
        r = regex_has_tag(text)
        p = parser_has_tag(text)
        if r != p:
            disagreements.append((source, text, r, p))

    print(f"Disagreements found: {len(disagreements)}")
    print()

    # Group by source category for a quick shape of where disagreements cluster
    by_category = {}
    for source, text, r, p in disagreements:
        by_category.setdefault(source, []).append((text, r, p))

    for source, items in sorted(by_category.items()):
        print(f"--- {source}: {len(items)} disagreement(s) ---")
        for text, r, p in items[:8]:
            print(f"  regex={r!s:5} parser={p!s:5}  input={text!r}")
        if len(items) > 8:
            print(f"  ... and {len(items) - 8} more")
        print()

    # Dump full log to file for the written report
    with open("tests/AiTesting/disagreements_log.jsonl", "w", encoding="utf-8") as f:
        for source, text, r, p in disagreements:
            f.write(json.dumps({
                "source": source, "text": text,
                "regex_has_tag": r, "parser_has_tag": p,
            }) + "\n")

    return disagreements


if __name__ == "__main__":
    main()
