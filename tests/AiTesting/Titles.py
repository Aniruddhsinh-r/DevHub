import re
import sys
import random
import string
import emoji
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
# Title domain: regexes and helpers
# ---------------------------------------------------------------------------

# Zero-width / invisible Unicode characters that can be used to bypass checks
INVISIBLE_CHARS = re.compile(r'[\u200b\u200c\u200d\ufeff\u2060]')

# Markdown link/image injection, e.g. ![x](javascript:...) or [x](javascript:...)
MARKDOWN_INJECTION = re.compile(r'!?\[[^\]]*\]\([^)]*\)')

EMAIL_PATTERN = re.compile(r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}')
PHONE_PATTERN = re.compile(r'(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}')


def clean_invisible(text: str) -> str:
    """Remove invisible/zero-width characters."""
    return INVISIBLE_CHARS.sub(' ', text).strip()

# ---------------------------------------------------------------------------
# Title domain: RULES
# ---------------------------------------------------------------------------

RULES = [
    {
        "id": "Rule A",
        "severity": "critical",
        "desc": "Title field is missing or not a string",
        "check": lambda title, item: title is None or not isinstance(title, str),
        "stop_on_fail": True,
    },
    {
        "id": "Rule 1",
        "severity": "critical",
        "desc": "Title is empty or contains only whitespace",
        "check": lambda title, item: len(clean_invisible(title).strip()) == 0,
    },
    {
        "id": "Rule 2",
        "severity": "critical",
        "desc": lambda title, item: f"Title length ({len(clean_invisible(title))}) is less than minimum 6 characters",
        "check": lambda title, item: len(clean_invisible(title)) < 6,
    },
    {
        "id": "Rule 3",
        "severity": "critical",
        "desc": lambda title, item: f"Title length ({len(clean_invisible(title))}) exceeds maximum 50 characters",
        "check": lambda title, item: len(clean_invisible(title)) > 50,
    },
    {
        "id": "Rule 4",
        "severity": "critical",
        "desc": lambda title, item: f"Contains invisible characters ({len(INVISIBLE_CHARS.findall(title))} found)",
        "check": lambda title, item: len(INVISIBLE_CHARS.findall(title)) > 0,
    },
    {
        "id": "Rule 5",
        "severity": "critical",
        "desc": "Contains markdown link/image injection",
        "check": lambda title, item: bool(MARKDOWN_INJECTION.search(title)),
    },
    {
        "id": "Rule 6",
        "severity": "critical",
        "desc": "Contains forbidden HTML tags",
        "check": lambda excerpt, item: has_forbidden_html_tag(excerpt),
    },
    {
        "id": "Rule 7",
        "severity": "warning",
        "desc": "Contains embedded newline/tab characters",
        "check": lambda title, item: bool(re.search(r'[\n\r\t]', title)),
    },
    {
        "id": "Rule 8",
        "severity": "warning",
        "desc": "Title has repeated word",
        "check": lambda title, item: (
            len(w := [t.lower().strip('.,!?;:') for t in title.split() if t.strip('.,!?;:')]) >= 3
            and Counter(w).most_common(1)[0][1] / len(w) > 0.5
        ),
    },
    {
        "id": "Rule 9",
        "severity": "critical",
        "desc": "Contains personal identifiable information (email address or phone number)",
        "check": lambda title, item: bool(EMAIL_PATTERN.search(title) or PHONE_PATTERN.search(title)),
    },
    {
        "id": "Rule 10",
        "severity": "warning",
        "desc": "Title is emoji-only, no real text",
        "check": lambda excerpt, item: (bool(excerpt.strip()) and not re.search(r'[a-zA-Z0-9]', excerpt) and
            (len(excerpt) <= 10000 and not emoji.replace_emoji(excerpt, replace='').strip()) or
            (len(excerpt) > 10000 and bool(re.search(r'[\U0001F300-\U0001FAFF\U00002600-\U000027BF]', excerpt)))
        ),
    },
]

VALUE_FIELD = "title"

# --- Metamorphic-test tuning for this domain ---
TRUNCATION_TRIGGER_RULE = "Rule 3"  # Max length rule
WHITESPACE_IGNORE_RULES = {"Rule 3"}  # Trailing spaces don't affect length if cleaned
MONOTONIC_RULES = {"Rule 5", "Rule 6", "Rule 9"}  # Rules that shouldn't disappear when extending
EXTEND_TEXT = " and here is extra text appended to the title"

DUMMY_ITEM = {"user_id": "test_user_123", "post_id": "post_456"}

GUARANTEED_FUZZ_CASES = [
    "",  # Empty
    "Hi",  # Too short
    "This is a very long title that exceeds the fifty character maximum limit for sure",  # Too long
    "<script>alert(1)</script>",  # HTML injection
    "![image](https://example.com/pic.png)",  # Markdown image
]


# ---------------------------------------------------------------------------
# Title domain: test helpers (generators, invariants, truncation, corpus)
# ---------------------------------------------------------------------------

def generate_random_title():
    """Generates random adversarial string inputs for fuzz testing."""
    strategies = [
        lambda: "".join(random.choices(string.ascii_letters + string.digits + " \n\t", k=random.randint(0, 60))),
        lambda: "<" + "".join(random.choices(string.ascii_letters, k=5)) + ">" + "text</script>",
        lambda: "![image](" + "".join(random.choices(string.ascii_letters, k=10)) + ")",
        lambda: "".join(random.choices(["\u200b", "\u200c", "\u200d", "\ufeff", "\xa0"], k=3)) + "title",
        lambda: "user" + str(random.randint(10, 99)) + "@example.com",
        lambda: "".join(random.choices(string.ascii_letters, k=random.randint(1, 5))),  # Too short
        lambda: "".join(random.choices(string.ascii_letters, k=random.randint(51, 60))),  # Too long
    ]
    return random.choice(strategies)()


def check_invariants(rules, title, item):
    """Checks critical invariants against the title validator rules."""
    violations = []
    try:
        case_failures, rule_ids = check_rules(rules, item, title)
    except Exception as e:
        violations.append(f"CRASHED: {type(e).__name__}: {e}")
        return violations

    # Invariant: Empty string should trigger Rule 1
    if title == "" and "Rule 1" not in rule_ids:
        violations.append("Empty string did not trigger Rule 1")

    # Invariant: Too short should trigger Rule 2
    if isinstance(title, str) and 1 <= len(clean_invisible(title)) <= 5 and "Rule 2" not in rule_ids:
        violations.append("Title shorter than 6 chars did not trigger Rule 2")

    # Invariant: Too long should trigger Rule 3
    if isinstance(title, str) and len(clean_invisible(title)) > 50 and "Rule 3" not in rule_ids:
        violations.append("Title longer than 50 chars did not trigger Rule 3")

    # Invariant: HTML tags should trigger Rule 6
    if isinstance(title, str) and re.search(r"<\s*script\b", title, re.IGNORECASE) and "Rule 6" not in rule_ids:
        violations.append("String containing a <script> tag did not trigger Rule 6")

    # Invariant: Invisible chars should trigger Rule 4
    if isinstance(title, str) and INVISIBLE_CHARS.search(title) and "Rule 4" not in rule_ids:
        violations.append("Title containing invisible characters did not trigger Rule 4")

    # Invariant: Personal data should trigger Rule 9
    if isinstance(title, str) and (EMAIL_PATTERN.search(title) or PHONE_PATTERN.search(title)) and "Rule 9" not in rule_ids:
        violations.append("Title containing personal data did not trigger Rule 9")

    return violations


def truncate_under_50(text: str) -> str:
    """Truncates text to under 50 characters while preserving structure."""
    clean_text = clean_invisible(text)
    if len(clean_text) <= 50:
        return clean_text
    return clean_text[:45]  # Truncate to 45 to be safely under 50


def _metamorphic_corpus(file_path):
    """Base cases to run every relation over: real titles from the file,
    hand-picked seeds guaranteed to satisfy each relation's precondition,
    plus random fuzzed strings for breadth."""
    corpus = []

    # Load from file if available
    cases, err = load_cases(file_path)
    if not err:
        for item in cases:
            title = item.get(VALUE_FIELD)
            if isinstance(title, str) and title != "":
                # If expected_failures not in file, use empty list
                if "expected_failures" not in item:
                    item["expected_failures"] = []
                corpus.append((item, title))

    # Comprehensive test cases with expected_failures
    # These test BOTH: rules that should fire AND rules that should stay quiet
    test_cases = [
        # VALID TITLES - all rules stay quiet (tests force-true mutation)
        {"title": "My Awesome Blog Post", "expected_failures": []},
        {"title": "Top 10 Programming Tips for 2024", "expected_failures": []},
        {"title": "Hello, World! Welcome to My Blog", "expected_failures": []},
        {"title": "The Quick Brown Fox Jumps Over The Lazy Dog", "expected_failures": []},
        {"title": "C# Programming & .NET Framework", "expected_failures": []},
        {"title": "123456", "expected_failures": []},  # Exactly min length (6)
        {"title": "A" * 50, "expected_failures": []},  # Exactly max length (50)

        # RULE 1: Empty title
        {"title": "", "expected_failures": ["Rule A", "Rule 1"]},
        {"title": "   ", "expected_failures": ["Rule 1"]},

        # RULE 2: Too short
        {"title": "Hi", "expected_failures": ["Rule 2"]},
        {"title": "Test", "expected_failures": ["Rule 2"]},
        {"title": "A" * 5, "expected_failures": ["Rule 2"]},
        {"title": " Test ", "expected_failures": ["Rule 2"]},

        # RULE 3: Too long
        {"title": "This is a very long title that exceeds the fifty character maximum limit for sure", "expected_failures": ["Rule 3"]},
        {"title": "A" * 55, "expected_failures": ["Rule 3"]},
        {"title": "This title is intentionally written to exceed the fifty character maximum limit" * 2, "expected_failures": ["Rule 3"]},

        # RULE 4: Invisible characters
        {"title": "\u200bHidden\u200cText\u200d", "expected_failures": ["Rule 4"]},
        {"title": "Normal\u200bTitle", "expected_failures": ["Rule 4"]},
        {"title": "\u200b\u200c\u200dTitle", "expected_failures": ["Rule 4"]},

        # RULE 5: Markdown image injection
        {"title": "![avatar](https://example.com/pic.png)", "expected_failures": ["Rule 5"]},
        {"title": "![profile pic](https://example.com/photo.jpg)", "expected_failures": ["Rule 5"]},

        # RULE 6: HTML tags
        {"title": "<script>alert(1)</script>", "expected_failures": ["Rule 6"]},
        {"title": "<div class='test'>Title</div>", "expected_failures": ["Rule 6"]},
        {"title": "<b>Bold</b> Title", "expected_failures": ["Rule 6"]},

        # RULE 7: Newline/tab characters (warning)
        {"title": "Title\nwith\nnewlines", "expected_failures": ["Rule 7"]},
        {"title": "Title\twith\ttabs", "expected_failures": ["Rule 7"]},

        # RULE 8: Repeated words (warning)
        {"title": "Hello hello hello world", "expected_failures": ["Rule 8"]},
        {"title": "Test, test, test, test", "expected_failures": ["Rule 8"]},

        # RULE 9: PII (email/phone)
        {"title": "Contact user@example.com", "expected_failures": ["Rule 9"]},
        {"title": "Call 555-123-4567", "expected_failures": ["Rule 9"]},
        {"title": "Email me@domain.com", "expected_failures": ["Rule 9"]},

        # MULTIPLE FAILURES - test multiple rules firing together
        {"title": "", "expected_failures": ["Rule A", "Rule 1"]},
        {"title": "<b>Hi</b>", "expected_failures": ["Rule 2", "Rule 6"]},
        {"title": "This is a very long title that has an email user@example.com inside it", "expected_failures": ["Rule 3", "Rule 9"]},
        {"title": "\u200b![image](https://example.com/pic.png)\u200c", "expected_failures": ["Rule 4", "Rule 5"]},
        {"title": "<script>short</script>", "expected_failures": ["Rule 2", "Rule 6"]},
        {"title": "A" * 55 + " user@example.com", "expected_failures": ["Rule 3", "Rule 9"]},
    ]

    # Add all test cases to corpus
    for test_case in test_cases:
        title = test_case.get(VALUE_FIELD)
        if isinstance(title, str):
            corpus.append((test_case, title))

    # Add random fuzzed strings for breadth (these won't have expected_failures)
    for _ in range(200):
        title = generate_random_title()
        item = {VALUE_FIELD: title}
        corpus.append((item, title))

    return corpus

# ---------------------------------------------------------------------------
# Grading + CLI
# ---------------------------------------------------------------------------

def _title_fn(item, index):
    return f"Title #{index}"


def grade_titles(file_path="tests/AiTesting/Titles.json"):
    """Validates titles loaded from a JSON file."""
    return grade_cases(
        file_path, RULES, VALUE_FIELD,
        header_label="TITLE EVALUATION RESULTS",
        title_fn=_title_fn,
        show_severity_counts=False,
    )


if __name__ == "__main__":
    file_path = sys.argv[1] if len(sys.argv) > 1 else "tests/AiTesting/Titles.json"

    if len(sys.argv) > 2 and sys.argv[2] == "--mutation":
        ok = run_mutation_test(file_path, RULES, VALUE_FIELD)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 1 and sys.argv[1] == "--fuzz":
        num_cases = int(sys.argv[2]) if len(sys.argv) > 2 else 500
        ok = run_fuzz_test(RULES, generate_random_title, check_invariants,
                            DUMMY_ITEM, GUARANTEED_FUZZ_CASES, num_cases)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--metamorphic":
        corpus = _metamorphic_corpus(file_path)
        ok = run_metamorphic_test(
            RULES, corpus, truncate_under_50,
            TRUNCATION_TRIGGER_RULE, WHITESPACE_IGNORE_RULES, MONOTONIC_RULES,
            EXTEND_TEXT,
        )
        sys.exit(0 if ok else 1)
    else:
        ok = grade_titles(file_path)
        sys.exit(0 if ok else 1)

