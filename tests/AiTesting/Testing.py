import random
import re
import sys
import emoji
from collections import Counter

from engine import (
    check_rules,
    grade_cases,
    run_mutation_test,
    run_fuzz_test,
    run_metamorphic_test,
    run_fake_ai_pipeline,
    run_consistency_check,
    measure_rule_performance,
    load_cases,
)

# ---------------------------------------------------------------------------
# Article/excerpt domain: regexes
# ---------------------------------------------------------------------------

# Zero-width / invisible Unicode characters that can be used to fake length
INVISIBLE_CHARS = re.compile(r'[\u200b\u200c\u200d\ufeff\u2060]')

# RTL/LTR override & directional control characters (visual scrambling attack)
DIRECTIONAL_OVERRIDES = re.compile(r'[\u202a-\u202e\u2066-\u2069]')

# Markdown link/image injection, e.g. ![x](javascript:...) or [x](javascript:...)
MARKDOWN_INJECTION = re.compile(r'!?\[[^\]]*\]\([^)]*\)')

# Emoji-only detection (rough range covering common emoji blocks)
EMOJI_PATTERN = re.compile(
    r'[\U0001F300-\U0001FAFF\U00002600-\U000027BF\U0001F1E6-\U0001F1FF]'
)


def clean_invisible(text):
    """Strip invisible/zero-width characters, collapsing them to a space."""
    return INVISIBLE_CHARS.sub(' ', text).strip()


# ---------------------------------------------------------------------------
# Article/excerpt domain: RULES (unchanged from before the refactor)
# ---------------------------------------------------------------------------

RULES = [
    {
        "id": "Additional Rule A",
        "severity": "critical",
        "desc": "Excerpt field is missing or not a string",
        "check": lambda excerpt, item: excerpt is None or not isinstance(excerpt, str),
        "stop_on_fail": True,
    },
    {
        "id": "Rule 1",
        "severity": "warning",
        "desc": "Excerpt is empty",
        "check": lambda excerpt, item: len(excerpt.strip()) == 0,
    },
    {
        "id": "Rule 2",
        "severity": "warning",
        "desc": lambda excerpt, item: (
            f"Only {len(clean_invisible(excerpt).split())} word(s) — needs at least 2 meaningful words"
        ),
        "check": lambda excerpt, item: len(clean_invisible(excerpt).split()) < 2,
    },
    {
        "id": "Rule 3",
        "severity": "warning",
        "desc": lambda excerpt, item: (
            f"Length ({len(clean_invisible(excerpt))}) exceeds 250 characters"
        ),
        "check": lambda excerpt, item: len(clean_invisible(excerpt)) > 250,
    },
    {
        "id": "Rule 4",
        "severity": "critical",
        "desc": "Contains forbidden HTML tags",
        "check": lambda excerpt, item: bool(re.search(r"<\s*/?\s*[a-zA-Z][\w-]*[^>]*>", excerpt)),
    },
    {
        "id": "Rule 5",
        "severity": "warning",
        "desc": "Excerpt is identical to the full body (not a summary)",
        "check": lambda excerpt, item: (
            bool(item.get("body", "")) and excerpt.strip() == item.get("body", "").strip()
        ),
    },
    {
        "id": "Rule 6",
        "severity": "critical",
        "desc": lambda excerpt, item: (
            f"Excerpt contains invisible characters "
            f"({len(INVISIBLE_CHARS.findall(excerpt))} found)"
        ),
        "check": lambda excerpt, item: (
            len(INVISIBLE_CHARS.findall(excerpt)) > 0
        ),
    },
    {
        "id": "Rule 7",
        "severity": "critical",
        "desc": "Contains RTL/LTR directional override characters",
        "check": lambda excerpt, item: bool(DIRECTIONAL_OVERRIDES.search(excerpt)),
    },
    {
        "id": "Rule 8",
        "severity": "warning",
        "desc": "Contains embedded newline/tab characters",
        "check": lambda excerpt, item: bool(re.search(r'[\n\r\t]', excerpt)),
    },
    {
        "id": "Rule 9",
        "severity": "critical",
        "desc": "Contains markdown link/image injection",
        "check": lambda excerpt, item: bool(MARKDOWN_INJECTION.search(excerpt)),
    },
    {
        "id": "Rule 10",
        "severity": "warning",
        "desc": "Excerpt is emoji-only, no real text",
        "check": lambda excerpt, item: (bool(excerpt.strip()) and not re.search(r'[a-zA-Z0-9]', excerpt) and
            (len(excerpt) <= 10000 and not emoji.replace_emoji(excerpt, replace='').strip()) or
            (len(excerpt) > 10000 and bool(re.search(r'[\U0001F300-\U0001FAFF\U00002600-\U000027BF]', excerpt)))
        ),
    },
    {
        "id": "Rule 11",
        "severity": "warning",
        "desc": "Excerpt is identical to the title",
        "check": lambda excerpt, item: (
            bool(item.get("title", ""))
            and excerpt.strip().lower() == item.get("title", "").strip().lower()
        ),
    },
    {
        "id": "Rule 12",
        "severity": "warning",
        "desc": "Excerpt is dominated by one repeated word",
        "check": lambda excerpt, item: (
            len(w := [t.lower().strip('.,!?;:') for t in excerpt.split() if t.strip('.,!?;:')]) >= 3
            and Counter(w).most_common(1)[0][1] / len(w) > 0.5
        ),
    },
    # --- Added by Task 8's adversarial search ---
    # Each of these exists because a generated adversarial excerpt passed
    # every prior rule while being obviously bad to a human. See
    # TASK8_REPORT.md for the search that found them and the reasoning
    # for adding exactly these three (and not more).
    {
        "id": "Rule 13",
        "severity": "warning",
        "desc": "Excerpt shares no vocabulary with the article's title or body (likely off-topic or placeholder text)",
        "check": lambda excerpt, item: (
            bool(w := [t for t in re.findall(r"[a-zA-Z']+", excerpt.lower()) if t not in _OVERLAP_STOPWORDS])
            and not any(
                t in set(
                    x for x in re.findall(r"[a-zA-Z']+", (item.get("title", "") + " " + item.get("body", "")).lower())
                    if x not in _OVERLAP_STOPWORDS
                )
                for t in w
            )
        ),
    },
    {
        "id": "Rule 14",
        "severity": "warning",
        "desc": lambda excerpt, item: (
            f"Excerpt is dominated by generic filler/clickbait words "
            f"({sum(1 for t in [x.strip('.,!?;:').lower() for x in excerpt.split() if x.strip('.,!?;:')] if t in _FILLER_WORDS)}"
            f"/{len([x for x in excerpt.split() if x.strip('.,!?;:')])})"
        ),
        "check": lambda excerpt, item: (
            len(w := [t.strip('.,!?;:').lower() for t in excerpt.split() if t.strip('.,!?;:')]) >= 3
            and sum(1 for t in w if t in _FILLER_WORDS) / len(w) >= 0.3
        ),
    },
    {
        "id": "Rule 15",
        "severity": "warning",
        "desc": "Excerpt is dominated by one repeated word-pair",
        "check": lambda excerpt, item: (
            len(w := [t.strip('.,!?;:').lower() for t in excerpt.split() if t.strip('.,!?;:')]) >= 5
            and bool(bigrams := list(zip(w, w[1:])))
            and Counter(bigrams).most_common(1)[0][1] / len(bigrams) > 0.5
        ),
    },
]

_OVERLAP_STOPWORDS = {
    "the", "a", "an", "and", "or", "of", "to", "in", "on", "for", "is",
    "are", "this", "that", "with", "by", "as", "at", "it", "its", "from",
    "be", "was", "were", "has", "have", "had", "not", "but", "into",
    "their", "these", "those", "than", "then", "such", "also", "which",
}a

_FILLER_WORDS = {
    "click", "here", "read", "more", "find", "out", "below", "today",
    "learn", "things", "thing", "stuff", "topic", "now", "right",
}

VALUE_FIELD = "excerpt"

# --- Metamorphic-test tuning for this domain ---
TRUNCATION_TRIGGER_RULE = "Rule 3"
WHITESPACE_IGNORE_RULES = set()  # excerpts require exact equality on padding
MONOTONIC_RULES = {"Rule 4", "Rule 7", "Rule 9"}
EXTEND_TEXT = " and here is some extra plain text appended after it"

DUMMY_ITEM = {"title": "Same Title Same Title", "body": "some body text here"}

GUARANTEED_FUZZ_CASES = [
    "",
    "<script>alert(1)</script>",
    "< script >alert(1)< /script >",
    "<ScRiPt>bad()</ScRiPt>",
]

PERFORMANCE_DUMMY_ITEM = {
    "title": "Performance Test",
    "body": "Test body",
    "expected_failures": ["Rule 3"],
}


# ---------------------------------------------------------------------------
# Article/excerpt domain: test helpers (generators, fake-ai, invariants, corpus)
# ---------------------------------------------------------------------------

def fake_ai(article):
    """A stand-in for a real excerpt-generation model, used to stress-test
    the rules with realistic-ish (and occasionally bad) output."""
    body = article.get("body", "")
    title = article.get("title", "")
    first_sentence = body.split(".")[0].strip() if body else ""

    outcome = random.choice([
        "good", "good", "good",
        "empty", "too_long", "html", "emoji_only", "title_repeat",
    ])

    if outcome == "good":
        return (first_sentence[:247] + "...") if len(first_sentence) > 247 else first_sentence
    if outcome == "empty":
        return ""
    if outcome == "too_long":
        return body[:300]
    if outcome == "html":
        return f"<b>{first_sentence[:30]}</b>"
    if outcome == "emoji_only":
        return "🔥🚀😀🎉"
    if outcome == "title_repeat":
        return title
    return first_sentence


def generate_random_excerpt():
    """Return one random, possibly-adversarial excerpt string."""
    generators = [
        lambda: "",
        lambda: " " * random.randint(0, 20),
        lambda: "".join(chr(random.randint(32, 126)) for _ in range(random.randint(0, 300))),
        lambda: "".join(chr(random.randint(0x00, 0x10FFFF)) for _ in range(random.randint(0, 30))
                         if chr(random.randint(0x00, 0x10FFFF)).isprintable() or True),
        lambda: "x" * random.randint(100, 5000),
        lambda: random.choice([
            "<script>alert(1)</script>", "< script >alert(1)< /script >",
            "<ScRiPt>bad()</ScRiPt>", "<b>bold</b>", "<div onclick='x()'>",
            "<img src=x onerror=alert(1)>", "no html here at all",
        ]),
        lambda: "".join(random.choice(["🔥", "😀", "🚀", "🎉", "💀", "a", "b", " "]) for _ in range(random.randint(0, 15))),
        lambda: "Hi" + "\u200b" * random.randint(0, 20),
        lambda: "text " + "\u202e" + "reversed" + "\u202c",
        lambda: "line1\nline2\ttabbed",
        lambda: "click ![img](javascript:alert(1))",
        lambda: "Same Title Same Title",
    ]
    return random.choice(generators)()


def check_invariants(rules, excerpt, item):
    violations = []
    try:
        case_failures, rule_ids = check_rules(rules, item, excerpt)
    except Exception as e:
        violations.append(f"CRASHED: {type(e).__name__}: {e}")
        return violations

    if excerpt == "" and "Rule 1" not in rule_ids:
        violations.append("Empty string did not trigger Rule 1")

    if re.search(r"<\s*script\b", excerpt, re.IGNORECASE) and "Rule 4" not in rule_ids:
        violations.append("String containing a <script> tag did not trigger Rule 4")

    return violations


def truncate_under_250(excerpt):
    """Cut an excerpt so clean_invisible(result) is under 250 chars,
    trimming at the last word boundary so truncation doesn't itself
    accidentally trip Rule 2 (too few words) as a side effect."""
    cleaned = clean_invisible(excerpt)
    truncated = cleaned[:245]
    if ' ' in truncated:
        candidate = truncated[:truncated.rfind(' ')]
        if len(candidate.split()) >= 2:
            truncated = candidate
    return truncated


def _metamorphic_corpus(file_path):
    """Base cases to run every relation over: real excerpts from the file,
    hand-picked seeds guaranteed to satisfy each relation's precondition
    (so the relation actually gets exercised, not skipped), plus random
    fuzzed strings for breadth."""
    corpus = []

    cases, err = load_cases(file_path)
    if not err:
        for item in cases:
            excerpt = item.get(VALUE_FIELD)
            if isinstance(excerpt, str) and excerpt != "":
                corpus.append((item, excerpt))

    seeds = [
        "x" * 300,
        "This sentence is intentionally a bit longer than two hundred and fifty characters total. " * 5,
        "<b>bold html tag that is long enough to also be too long for rule three</b> " * 5,
        "text " + "\u202e" + "reversed override that also runs long enough to trip length" + "\u202c " * 5,
        "see [2020](https://ex.com/preprint) markdown injection padded out long enough " * 5,
        "no problems here just a normal short excerpt",
    ]
    for s in seeds:
        corpus.append((DUMMY_ITEM, s))

    for _ in range(200):
        corpus.append((DUMMY_ITEM, generate_random_excerpt()))

    return corpus


def performance_nasty_inputs():
    return [
        ("Thousands of < with no >", "<" * 1000000),
        ("Deeply repeated pattern", "ab" * 1000),
        ("Huge runs of invisible chars", "\u200b" * 50),
        ("Nested brackets", "(" * 500000 + ")" * 50),
        ("Mixed HTML-like", "<div " * 250000 + ">" * 250000),
        ("Long repeated word", "test " * 200000),
    ]


# ---------------------------------------------------------------------------
# Grading + CLI
# ---------------------------------------------------------------------------

def _title_fn(item, index):
    return item.get("title", f"<untitled #{index}>")


def grade_excerpts(file_path="Articles.json"):
    return grade_cases(
        file_path, RULES, VALUE_FIELD,
        header_label="SUMMARY RESULTS",
        title_fn=_title_fn,
        show_severity_counts=True,
    )


if __name__ == "__main__":
    file_path = sys.argv[1] if len(sys.argv) > 1 else "tests/AiTesting/Articles.json"

    min_pass_rate = 0.8
    if "--min-pass-rate" in sys.argv:
        idx = sys.argv.index("--min-pass-rate")
        if idx + 1 < len(sys.argv):
            min_pass_rate = float(sys.argv[idx + 1])

    if len(sys.argv) > 2 and sys.argv[2] == "--fake-ai":
        ok = run_fake_ai_pipeline(file_path, RULES, fake_ai)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--consistency":
        ok = run_consistency_check(file_path, RULES, fake_ai, min_pass_rate=min_pass_rate)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 1 and sys.argv[1] == "--fuzz":
        num_cases = int(sys.argv[2]) if len(sys.argv) > 2 else 500
        ok = run_fuzz_test(RULES, generate_random_excerpt, check_invariants,
                            DUMMY_ITEM, GUARANTEED_FUZZ_CASES, num_cases)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--mutation":
        ok = run_mutation_test(file_path, RULES, VALUE_FIELD)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--metamorphic":
        corpus = _metamorphic_corpus(file_path)
        ok = run_metamorphic_test(
            RULES, corpus, truncate_under_250,
            TRUNCATION_TRIGGER_RULE, WHITESPACE_IGNORE_RULES, MONOTONIC_RULES,
            EXTEND_TEXT,
        )
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 1 and sys.argv[1] == "--pattern":
        ok = measure_rule_performance(RULES, PERFORMANCE_DUMMY_ITEM, performance_nasty_inputs())
        sys.exit(0 if ok else 1)
    else:
        ok = grade_excerpts(file_path)
        sys.exit(0 if ok else 1)
