import re
import sys
import json
import random
import string
from collections import Counter

# Zero-width / invisible Unicode characters that can be used to bypass checks
INVISIBLE_CHARS = re.compile(r'[\u200b\u200c\u200d\ufeff\xa0\u2060]')

# Markdown image injection only, e.g. ![alt](https://...) or ![alt](javascript:...)
MARKDOWN_IMAGE_INJECTION = re.compile(r'!\[[^\]]*\]\([^)]*\)')

# Regex for detecting email addresses
EMAIL_PATTERN = re.compile(r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}')

# Regex for detecting standard and international phone numbers
PHONE_PATTERN = re.compile(r'(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}')


def clean_invisible(text: str) -> str:
    """Remove invisible/zero-width characters."""
    return INVISIBLE_CHARS.sub('', text)


# Define validation rules
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
        "check": lambda excerpt, item: bool(re.search(r"<\s*/?\s*[a-zA-Z][\w-]*[^>]*>", excerpt)),
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


def check_comment_rules(item: dict, comment: str):
    """Evaluates a comment against all defined rules."""
    case_failures = []
    rule_ids = []

    for rule in RULES:
        if rule["check"](comment, item):
            desc = rule["desc"](comment, item) if callable(rule["desc"]) else rule["desc"]
            case_failures.append(f"{rule['id']} Failed: {desc}")
            rule_ids.append(rule["id"])
            if rule.get("stop_on_fail"):
                return case_failures, rule_ids

    return case_failures, rule_ids


def print_rule_accuracy_table(rule_stats):
    """Prints the evaluation matrix table."""
    print("\n" + "=" * 40)
    print("RULE ACCURACY (evaluating the evaluator)")
    print("=" * 40)
    print(f"{'Rule':<20}{'Expected':>9}{'Caught':>8}{'Recall':>9}{'False Alarms':>14}")

    for rule_id, stats in rule_stats.items():
        expected = stats["expected"]
        tp = stats["tp"]
        fp = stats["fp"]
        recall = f"{(tp / expected * 100):.0f}%" if expected > 0 else "N/A"
        print(f"{rule_id:<20}{expected:>9}{tp:>8}{recall:>9}{fp:>14}")

    weak_rules = [
        rid for rid, s in rule_stats.items()
        if s["expected"] > 0 and s["tp"] / s["expected"] < 0.5
    ]
    if weak_rules:
        print(f"\n⚠ Weak rule(s) (recall < 50%): {weak_rules}")


def grade_comments(file_path: str = "tests/AiTesting/Comments.json"):
    """Validates comments loaded from a JSON file."""
    try:
        with open(file_path, "r", encoding="utf-8") as f:
            cases = json.load(f)
    except Exception as e:
        print(f"Error reading '{file_path}': {e}")
        return False

    if not isinstance(cases, list):
        print("Invalid JSON format: expected a list of objects.")
        return False

    total = len(cases)
    passed_count = 0
    failures = []

    # --- ADDED: Harness Tracking Variables ---
    correct_count = 0
    no_expectation = []
    mismatches = []
    rule_stats = {rule["id"]: {"expected": 0, "tp": 0, "fp": 0} for rule in RULES}

    for index, item in enumerate(cases, start=1):
        comment = item.get("comment")
        case_failures, rule_ids = check_comment_rules(item, comment)

        if not case_failures:
            passed_count += 1
        else:
            failures.append((index, case_failures))

        # --- ADDED: Mismatch & Accuracy Evaluation Logic ---
        title_for_report = f"Comment #{index}"
        if "expected_failures" not in item:
            no_expectation.append((index, title_for_report))
        else:
            expected_ids = set(item["expected_failures"])
            actual_ids = set(rule_ids)

            if actual_ids == expected_ids:
                correct_count += 1
            else:
                missed = sorted(expected_ids - actual_ids)
                surprise = sorted(actual_ids - expected_ids)
                mismatches.append((index, title_for_report, missed, surprise))

            for rule_id in rule_stats:
                expected = rule_id in expected_ids
                fired = rule_id in actual_ids
                if expected:
                    rule_stats[rule_id]["expected"] += 1
                    if fired:
                        rule_stats[rule_id]["tp"] += 1
                elif fired:
                    rule_stats[rule_id]["fp"] += 1

    print("=" * 40)
    print(f"COMMENT EVALUATION RESULTS: Passed {passed_count}/{total}")
    print("=" * 40)

    if failures:
        print("\nFailed Cases:")
        for item_num, reasons in failures:
            print(f"\nItem #{item_num}:")
            for reason in reasons:
                print(f"  - {reason}")

    # --- ADDED: Self-Check & Accuracy Reports ---
    checked = total - len(no_expectation)
    print("\n" + "=" * 40)
    print(f"HARNESS SELF-CHECK: {correct_count}/{checked} cases matched their expected_failures")
    print("=" * 40)

    if no_expectation:
        print(f"\n⚠ {len(no_expectation)} case(s) missing 'expected_failures' (cannot verify):")
        for idx, t in no_expectation:
            print(f"  - Item #{idx}: {t}")

    if mismatches:
        print(f"\n❌ {len(mismatches)} mismatch(es):\n")
        for idx, t, missed, surprise in mismatches:
            print(f"Item #{idx}: {t}")
            if missed:
                print(f"  MISSED DETECTION -> expected to fail but passed: {missed}")
            if surprise:
                print(f"  SURPRISE FAILURE -> failed but wasn't expected to: {surprise}")
            print()
    elif checked:
        print("\n✅ Every case with expectations matched exactly.")

    print_rule_accuracy_table(rule_stats)

    return not mismatches and not no_expectation


def check_comment_rules_with_disabled(item: dict, comment: str, disabled_rule_id: str):
    """Evaluates a comment against rules while simulating a specific rule being disabled."""
    case_failures = []
    rule_ids = []

    for rule in RULES:
        if rule["id"] == disabled_rule_id:
            continue

        if rule["check"](comment, item):
            desc = rule["desc"](comment, item) if callable(rule["desc"]) else rule["desc"]
            case_failures.append(f"{rule['id']} Failed: {desc}")
            rule_ids.append(rule["id"])
            if rule.get("stop_on_fail"):
                return case_failures, rule_ids

    return case_failures, rule_ids


def run_mutation_test(file_path="tests/AiTesting/Comments.json"):
    """
    Mutation test: Disables each rule one by one to verify that
    at least one JSON test case detects its removal.
    """
    try:
        with open(file_path, "r", encoding="utf-8") as f:
            cases = json.load(f)
    except Exception as e:
        print(f"Error reading '{file_path}': {e}")
        return False

    print("=" * 40)
    print("MUTATION TEST: disabling each rule, one at a time")
    print("=" * 40)

    untested_rules = []

    for rule in RULES:
        rule_id = rule["id"]
        caught_by_at_least_one_case = False

        for item in cases:
            if "expected_failures" not in item:
                continue

            comment = item.get("comment")
            expected_ids = set(item["expected_failures"])

            try:
                _, mutated_rule_ids = check_comment_rules_with_disabled(item, comment, rule_id)
                actual_ids = set(mutated_rule_ids)
            except Exception:
                caught_by_at_least_one_case = True
                break

            if rule_id in expected_ids and rule_id not in actual_ids:
                caught_by_at_least_one_case = True
                break

        status = "✅ guarded" if caught_by_at_least_one_case else "❌ NO TEST CATCHES THIS"
        print(f"  {rule_id:<20} {status}")

        if not caught_by_at_least_one_case:
            untested_rules.append(rule_id)

    print("\n" + "=" * 40)
    if untested_rules:
        print(f"❌ {len(untested_rules)} rule(s) have zero test coverage: {untested_rules}")
        print("   (disabling them changes nothing — add a case whose")
        print("    expected_failures includes that rule id)")
    else:
        print("✅ Every rule is guarded by at least one test case.")
    print("=" * 40)

    return len(untested_rules) == 0


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


def check_invariants(comment, item):
    """Checks critical invariants against your comment validator rules."""
    violations = []
    try:
        case_failures, rule_ids = check_comment_rules(item, comment)
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



def run_fuzz_test(num_cases=500):
    """Fuzz tests the comment evaluator with random and guaranteed edge cases."""
    print("=" * 40)
    print(f"FUZZ TEST: {num_cases} random adversarial inputs")
    print("=" * 40)

    dummy_item = {
        "user_id": "test_user_123",
        "post_id": "post_456"
    }

    total_violations = 0
    crashes = 0

    guaranteed_cases = [
        "",
        "<script>alert(1)</script>",
        "< script >alert(1)< /script >",
        "<ScRiPt>bad()</ScRiPt>",
    ]

    for i, comment in enumerate(guaranteed_cases):
        violations = check_invariants(comment, dummy_item)
        if violations:
            total_violations += len(violations)
            for v in violations:
                if v.startswith("CRASHED"):
                    crashes += 1
                print(f"\n❌ Violation on guaranteed input #{i}:")
                print(f"   Input:     {comment!r}")
                print(f"   Violation: {v}")

    for i in range(num_cases):
        comment = generate_random_comment()
        violations = check_invariants(comment, dummy_item)
        if violations:
            total_violations += len(violations)
            for v in violations:
                if v.startswith("CRASHED"):
                    crashes += 1
                print(f"\n❌ Violation on random input #{i}:")
                print(f"   Input:     {comment!r}")
                print(f"   Violation: {v}")

    print("\n" + "=" * 40)
    total_cases = num_cases + len(guaranteed_cases)
    if total_violations == 0:
        print(f"✅ All {total_cases} inputs held every invariant. No bugs found.")
    else:
        print(f"❌ {total_violations} invariant violation(s) found across {total_cases} inputs ({crashes} crashes).")
    print("=" * 40)

    return total_violations == 0


def _truncate_under_500(text: str) -> str:
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

    try:
        with open(file_path, "r", encoding="utf-8") as f:
            cases = json.load(f)
        for item in cases:
            comment = item.get("comment")
            if isinstance(comment, str) and comment != "":
                corpus.append((item, comment))
    except Exception:
        pass

    dummy_item = {"user_id": "test_user_123", "post_id": "post_456"}

    seeds = [
        "c" * 550,
        "This comment is intentionally written to exceed the five hundred character threshold. " * 8,
        "<div>HTML element that is long enough to trip both length and tag checks</div> " * 8,
        "![avatar](https://example.com/pic.png) " * 15,
        "user_test_email_address@example.com " * 15,
        "A regular, completely harmless user comment that passes all checks.",
    ]
    for s in seeds:
        corpus.append((dummy_item, s))

    for _ in range(200):
        corpus.append((dummy_item, generate_random_comment()))

    return corpus


def run_metamorphic_test(file_path="tests/AiTesting/Comments.json"):
    """
    Metamorphic test: checks invariant relationships between comment inputs and outputs.
    """
    print("=" * 40)
    print("METAMORPHIC TEST: checking relationships between outputs")
    print("=" * 40)

    corpus = _metamorphic_corpus(file_path)

    stats = {
        "determinism": {"tested": 0, "violations": []},
        "truncation": {"tested": 0, "violations": []},
        "whitespace": {"tested": 0, "violations": []},
        "superstring": {"tested": 0, "violations": []},
    }

    for item, comment in corpus:
        if not isinstance(comment, str):
            continue

        try:
            _, base_rule_ids = check_comment_rules(item, comment)
        except Exception as e:
            stats["determinism"]["violations"].append(
                (comment, None, f"CRASHED on base comment: {type(e).__name__}: {e}")
            )
            continue
        base_set = set(base_rule_ids)

        # --- Relation 1: determinism ---
        stats["determinism"]["tested"] += 1
        _, repeat_rule_ids = check_comment_rules(item, comment)
        if set(repeat_rule_ids) != base_set:
            stats["determinism"]["violations"].append(
                (comment, None, f"first={sorted(base_set)} second={sorted(repeat_rule_ids)}")
            )

        # --- Relation 2: truncation ---
        if "Rule 2" in base_set:
            stats["truncation"]["tested"] += 1
            truncated = _truncate_under_500(comment)
            _, trunc_rule_ids = check_comment_rules(item, truncated)
            trunc_set = set(trunc_rule_ids)
            new_rules = trunc_set - base_set
            if "Rule 2" in trunc_set or new_rules:
                stats["truncation"]["violations"].append(
                    (comment,truncated,f"before={sorted(base_set)} after={sorted(trunc_set)} "
                        f"(Rule 2 still present: {'Rule 2' in trunc_set}, new rules: {sorted(new_rules)})",
                    )
                )

        # --- Relation 3: whitespace ---
        for pad in (1, 3, 10):
            stats["whitespace"]["tested"] += 1
            padded = comment + (" " * pad)
            _, pad_rule_ids = check_comment_rules(item, padded)
            pad_set = set(pad_rule_ids)

            diff = pad_set.symmetric_difference(base_set)
            if diff - {"Rule 2"}:
                stats["whitespace"]["violations"].append(
                    (
                        comment,
                        padded,
                        f"before={sorted(base_set)} after(+{pad} spaces)={sorted(pad_set)}",
                    )
                )

        # --- Relation 4: superstring monotonicity ---
        monotonic_rules = base_set & {"Rule 4", "Rule 5", "Rule 8"}
        if monotonic_rules:
            stats["superstring"]["tested"] += 1
            extended = comment + " and here is extra plain text appended to the comment"
            _, ext_rule_ids = check_comment_rules(item, extended)
            ext_set = set(ext_rule_ids)
            vanished = monotonic_rules - ext_set
            if vanished:
                stats["superstring"]["violations"].append(
                    (
                        comment,
                        extended,
                        f"rules {sorted(vanished)} disappeared after appending plain text",
                    )
                )

    total_violations = sum(len(s["violations"]) for s in stats.values())

    for name, label in [
        ("determinism", "Determinism (same comment graded twice)"),
        ("truncation", "Truncation (cut under 500 chars drops Rule 2, nothing new)"),
        ("whitespace", "Whitespace (trailing spaces don't change verdict)"),
        ("superstring", "Superstring monotonicity (Rule 4/5/8 can't vanish by appending text)"),
    ]:
        s = stats[name]
        n_bad = len(s["violations"])
        status = "✅" if n_bad == 0 else "❌"
        print(f"\n{status} {label}")
        print(f"   tested: {s['tested']}   violations: {n_bad}")
        for base, transformed, note in s["violations"][:3]:
            print(f"   - base={base!r}")
            if transformed is not None:
                print(f"     transformed={transformed!r}")
            print(f"     {note}")

    print("\n" + "=" * 40)
    if total_violations == 0:
        print(f"✅ All relations held across {len(corpus)} base cases.")
    else:
        print(f"❌ {total_violations} total violation(s) found. See above.")
    print("=" * 40)

    return total_violations == 0


if __name__ == "__main__":
    file_path = sys.argv[1] if len(sys.argv) > 1 else "tests/AiTesting/Comments.json"

    if len(sys.argv) > 2 and sys.argv[2] == "--mutation":
        ok = run_mutation_test(file_path)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 1 and sys.argv[1] == "--fuzz":
        num_cases = int(sys.argv[2]) if len(sys.argv) > 2 else 500
        ok = run_fuzz_test(num_cases)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--metamorphic":
            ok = run_metamorphic_test(file_path)
            sys.exit(0 if ok else 1)
    else:
        ok = grade_comments(file_path)
        sys.exit(0 if ok else 1)
