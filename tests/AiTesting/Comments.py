import re
import sys
import random
import string
from collections import Counter

from engine import (
    check_rules,
    grade_cases,
    run_mutation_test,
    run_fuzz_test,
    run_metamorphic_test,
    load_cases,
)

from html_compare import has_forbidden_html_tag

# ---------------------------------------------------------------------------
# Comment domain: regexes
# ---------------------------------------------------------------------------

# Zero-width / invisible Unicode characters that can be used to bypass checks
INVISIBLE_CHARS = re.compile(r'[\u200b\u200c\u200d\ufeff\xa0\u2060]')

# Markdown image injection only, e.g. ![alt](https://...) or ![alt](javascript:...)
MARKDOWN_IMAGE_INJECTION = re.compile(r'!\[[^\]]*\]\([^)]*\)')

EMAIL_PATTERN = re.compile(r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}')
PHONE_PATTERN = re.compile(r'(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}')


def clean_invisible(text: str) -> str:
    """Remove invisible/zero-width characters."""
    return INVISIBLE_CHARS.sub('', text)


# ---------------------------------------------------------------------------
# Comment domain: RULES (unchanged from before the refactor)
# ---------------------------------------------------------------------------

RULES = [
    {
        "id": "Rule A",
        "severity": "critical",
        "desc": "Comment field is missing or not a string",
        "check": lambda comment, item: comment is None or not isinstance(comment, str),
        "stop_on_fail": True,
    },
    {
        "id": "Rule 1",
        "severity": "critical",
        "desc": "Comment is empty or contains only whitespace",
        "check": lambda comment, item: len(clean_invisible(comment).strip()) == 0,
    },
    {
        "id": "Rule 2",
        "severity": "warning",
        "desc": lambda comment, item: f"Comment length ({len(clean_invisible(comment))}) exceeds 500 characters",
        "check": lambda comment, item: len(clean_invisible(comment)) > 500,
    },
    {
        "id": "Rule 3",
        "severity": "critical",
        "desc": lambda comment, item: f"Contains invisible characters ({len(INVISIBLE_CHARS.findall(comment))} found)",
        "check": lambda comment, item: len(INVISIBLE_CHARS.findall(comment)) > 0,
    },
    {
        "id": "Rule 4",
        "severity": "critical",
        "desc": "Contains markdown image injection",
        "check": lambda comment, item: bool(MARKDOWN_IMAGE_INJECTION.search(comment)),
    },
    {
        "id": "Rule 5",
        "severity": "critical",
        "desc": "Contains forbidden HTML tags",
        "check": lambda excerpt, item: has_forbidden_html_tag(excerpt),
    },
    {
        "id": "Rule 6",
        "severity": "warning",
        "desc": "Contains embedded newline/tab characters",
        "check": lambda excerpt, item: bool(re.search(r'[\n\r\t]', excerpt)),
    },
    {
        "id": "Rule 7",
        "severity": "warning",
        "desc": "Comment have repeated word",
        "check": lambda excerpt, item: (
            len(w := [t.lower().strip('.,!?;:') for t in excerpt.split() if t.strip('.,!?;:')]) >= 3
            and Counter(w).most_common(1)[0][1] / len(w) > 0.5
        ),
    },
    {
        "id": "Rule 8",
        "severity": "critical",
        "desc": "Contains personal identifiable information (email address or phone number)",
        "check": lambda comment, item: bool(EMAIL_PATTERN.search(comment) or PHONE_PATTERN.search(comment)),
    },
]

VALUE_FIELD = "comment"

# --- Metamorphic-test tuning for this domain ---
TRUNCATION_TRIGGER_RULE = "Rule 2"
WHITESPACE_IGNORE_RULES = {"Rule 2"}
MONOTONIC_RULES = {"Rule 4", "Rule 5", "Rule 8"}
EXTEND_TEXT = " and here is extra plain text appended to the comment"

DUMMY_ITEM = {"user_id": "test_user_123", "post_id": "post_456"}

GUARANTEED_FUZZ_CASES = [
    "",
    "<script>alert(1)</script>",
    "<ScRiPt>bad()</ScRiPt>",
]


# ---------------------------------------------------------------------------
# Comment domain: test helpers (generators, invariants, truncation, corpus)
# ---------------------------------------------------------------------------

def generate_random_comment():
    """Generates random adversarial string inputs for fuzz testing."""
    strategies = [
        lambda: "".join(random.choices(string.ascii_letters + string.digits + " \n\t", k=random.randint(0, 600))),
        lambda: "<" + "".join(random.choices(string.ascii_letters, k=5)) + ">" + "text</script>",
        lambda: "![image](" + "".join(random.choices(string.ascii_letters, k=10)) + ")",
        lambda: "".join(random.choices(["\u200b", "\u200c", "\u200d", "\ufeff", "\xa0"], k=5)) + "comment",
        lambda: "user" + str(random.randint(10, 99)) + "@example.com",
    ]
    return random.choice(strategies)()


def check_invariants(rules, comment, item):
    """Checks critical invariants against the comment validator rules."""
    violations = []
    try:
        case_failures, rule_ids = check_rules(rules, item, comment)
    except Exception as e:
        violations.append(f"CRASHED: {type(e).__name__}: {e}")
        return violations

    if comment == "" and "Rule 1" not in rule_ids:
        violations.append("Empty string did not trigger Rule 1")

    if isinstance(comment, str) and re.search(r"<\s*script\b", comment, re.IGNORECASE) and "Rule 5" not in rule_ids:
        violations.append("String containing a <script> tag did not trigger Rule 5")

    if isinstance(comment, str) and INVISIBLE_CHARS.search(comment) and "Rule 3" not in rule_ids:
        violations.append("Comment containing invisible characters did not trigger Rule 3")

    if isinstance(comment, str) and (EMAIL_PATTERN.search(comment) or PHONE_PATTERN.search(comment)) and "Rule 8" not in rule_ids:
        violations.append("Comment containing personal data did not trigger Rule 8")

    return violations


def truncate_under_500(text: str) -> str:
    """Truncates text to under 500 characters while preserving structure."""
    clean_text = clean_invisible(text)
    if len(clean_text) <= 500:
        return clean_text
    return clean_text[:450]


def _metamorphic_corpus(file_path):
    """Base cases to run every relation over: real comments from the file,
    hand-picked seeds guaranteed to satisfy each relation's precondition,
    plus random fuzzed strings for breadth."""
    corpus = []

    cases, err = load_cases(file_path)
    if not err:
        for item in cases:
            comment = item.get(VALUE_FIELD)
            if isinstance(comment, str) and comment != "":
                corpus.append((item, comment))

    seeds = [
        "c" * 550,
        "This comment is intentionally written to exceed the five hundred character threshold. " * 8,
        "<div>HTML element that is long enough to trip both length and tag checks</div> " * 8,
        "![avatar](https://example.com/pic.png) " * 15,
        "user_test_email_address@example.com " * 15,
        "A regular, completely harmless user comment that passes all checks.",
    ]
    for s in seeds:
        corpus.append((DUMMY_ITEM, s))

    for _ in range(200):
        corpus.append((DUMMY_ITEM, generate_random_comment()))

    return corpus


# ---------------------------------------------------------------------------
# Grading + CLI
# ---------------------------------------------------------------------------

def _title_fn(item, index):
    return f"Comment #{index}"


def grade_comments(file_path="tests/AiTesting/Comments.json"):
    """Validates comments loaded from a JSON file."""
    return grade_cases(
        file_path, RULES, VALUE_FIELD,
        header_label="COMMENT EVALUATION RESULTS",
        title_fn=_title_fn,
        show_severity_counts=False,
    )


if __name__ == "__main__":
    file_path = sys.argv[1] if len(sys.argv) > 1 else "tests/AiTesting/Comments.json"

    if len(sys.argv) > 2 and sys.argv[2] == "--mutation":
        ok = run_mutation_test(file_path, RULES, VALUE_FIELD)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 1 and sys.argv[1] == "--fuzz":
        num_cases = int(sys.argv[2]) if len(sys.argv) > 2 else 500
        ok = run_fuzz_test(RULES, generate_random_comment, check_invariants,
                            DUMMY_ITEM, GUARANTEED_FUZZ_CASES, num_cases)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--metamorphic":
        corpus = _metamorphic_corpus(file_path)
        ok = run_metamorphic_test(
            RULES, corpus, truncate_under_500,
            TRUNCATION_TRIGGER_RULE, WHITESPACE_IGNORE_RULES, MONOTONIC_RULES,
            EXTEND_TEXT,
        )
        sys.exit(0 if ok else 1)
    else:
        ok = grade_comments(file_path)
        sys.exit(0 if ok else 1)
