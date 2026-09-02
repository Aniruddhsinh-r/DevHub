import json
import random
import re
import sys

# Zero-width / invisible Unicode characters that can be used to fake length
INVISIBLE_CHARS = re.compile(r'[\u200b\u200c\u200d\ufeff\xa0\u2060]')

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


# --------------------------------------------------------------------------
# NEW: rule registry. Each rule is ONE entry: its id, severity, a
# description (string or a function that builds one from the excerpt),
# and a check function that returns True when the rule FAILS.
#
# This replaces 11 separate hand-written if-blocks and a disconnected
# RULE_SEVERITY dict. Adding a new rule = adding one entry here.
# Nothing else in the file needs to change.
# A rule is critical if it can deceive or harm a reader, or execute/run code.
# --------------------------------------------------------------------------
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
            f"Length ({len(clean_invisible(excerpt))}) is 6 or fewer characters"
        ),
        "check": lambda excerpt, item: len(clean_invisible(excerpt)) <= 6,
    },
    {
        "id": "Rule 3",
        "severity": "warning",
        "desc": lambda excerpt, item: (
            f"Length ({len(clean_invisible(excerpt))}) exceeds 50 characters"
        ),
        "check": lambda excerpt, item: len(clean_invisible(excerpt)) > 50,
    },
    {
        "id": "Rule 4",
        "severity": "critical",
        "desc": "Contains forbidden HTML tags",
        "check": lambda excerpt, item: bool(re.search(r"<[^>]+>", excerpt)),
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
            f"Excerpt is mostly invisible characters "
            f"({len(INVISIBLE_CHARS.findall(excerpt))}/{len(excerpt)})"
        ),
        "check": lambda excerpt, item: (
            len(excerpt) > 0
            and len(INVISIBLE_CHARS.findall(excerpt)) / len(excerpt) > 0.3
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
        "check": lambda excerpt, item: (
            bool(excerpt.strip()) and not EMOJI_PATTERN.sub('', excerpt).strip()
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
]

# Severity lookup built once from the registry, so nothing else needs
# a separate hardcoded dict.
RULE_SEVERITY = {rule["id"]: rule["severity"] for rule in RULES}


def check_excerpt_rules(item, excerpt):
    case_failures = []
    rule_ids = []

    # CHANGED: single loop over the whole registry, including
    # "Additional Rule A". If a rule marked stop_on_fail fires, later
    # rules are skipped (they'd crash calling .strip()/len() on a
    # non-string excerpt).
    for rule in RULES:
        if rule["check"](excerpt, item):
            desc = rule["desc"](excerpt, item) if callable(rule["desc"]) else rule["desc"]
            case_failures.append(f"{rule['id']} Failed: {desc}")
            rule_ids.append(rule["id"])
            if rule.get("stop_on_fail"):
                return case_failures, rule_ids

    return case_failures, rule_ids


def split_by_severity(rule_ids):
    critical = [r for r in rule_ids if RULE_SEVERITY.get(r) == "critical"]
    warning = [r for r in rule_ids if RULE_SEVERITY.get(r) == "warning"]
    return critical, warning


def fake_ai(article):
    body = article.get("body", "")
    title = article.get("title", "")
    first_sentence = body.split(".")[0].strip() if body else ""

    outcome = random.choice([
        "good", "good", "good",
        "empty", "too_long", "html", "emoji_only", "title_repeat",
    ])

    if outcome == "good":
        return (first_sentence[:47] + "...") if len(first_sentence) > 47 else first_sentence
    if outcome == "empty":
        return ""
    if outcome == "too_long":
        return body[:120]
    if outcome == "html":
        return f"<b>{first_sentence[:30]}</b>"
    if outcome == "emoji_only":
        return "🔥🚀😀🎉"
    if outcome == "title_repeat":
        return title
    return first_sentence


# --------------------------------------------------------------------------
# NEW (Exercise 2): print a per-rule accuracy table using ground truth
# (expected_failures) vs actual detections. For each rule:
#   expected = # cases where this rule SHOULD have fired
#   tp       = # of those cases where it actually fired (correct catch)
#   fp       = # cases where it fired but wasn't expected (false alarm)
#   recall   = tp / expected  -> how often it catches what it should
# --------------------------------------------------------------------------
def print_rule_accuracy_table(rule_stats):
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


def grade_excerpts(file_path="Articles.json"):
    try:
        with open(file_path, "r", encoding="utf-8") as f:
            cases = json.load(f)
    except Exception as e:
        print(f"Invalid JSON format in '{file_path}'. Error: {e}")
        return False

    if not isinstance(cases, list):
        print(f"Invalid JSON structure: root must be a list of cases, got {type(cases).__name__}.")
        return False

    total = len(cases)
    passed_count = 0
    failures = []

    correct_count = 0
    no_expectation = []
    mismatches = []

    total_critical = 0
    total_warning = 0

    # NEW (Exercise 2): per-rule accuracy tracking, seeded with every
    # known rule id so rules with zero test coverage still show up
    # in the table (0/0 recall = "no test proves this rule works").
    rule_stats = {rule["id"]: {"expected": 0, "tp": 0, "fp": 0} for rule in RULES}

    for index, item in enumerate(cases, start=1):
        excerpt = item.get("excerpt")
        case_failures, rule_ids = check_excerpt_rules(item, excerpt)

        critical_ids, warning_ids = split_by_severity(rule_ids)
        total_critical += len(critical_ids)
        total_warning += len(warning_ids)

        if not case_failures:
            passed_count += 1
        else:
            failures.append((index, case_failures))

        title_for_report = item.get("title", f"<untitled #{index}>")
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

            # NEW (Exercise 2): tally recall/false-alarm stats for every
            # rule against this case's ground truth.
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
    print(f"SUMMARY RESULTS: Passed {passed_count}/{total}")
    print(f"{total_critical} critical failure(s), {total_warning} warning(s)")
    print("=" * 40)

    if failures:
        print("\nFailed Cases:")
        for item_num, reasons in failures:
            print(f"\nItem #{item_num}:")
            for reason in reasons:
                print(f"  - {reason}")

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

    # NEW (Exercise 2): print the per-rule accuracy table
    print_rule_accuracy_table(rule_stats)

    return not mismatches and not no_expectation


def run_fake_ai_pipeline(file_path="Articles.json"):
    with open(file_path, "r", encoding="utf-8") as f:
        articles = json.load(f)

    total = len(articles)
    passed_count = 0
    total_critical = 0
    total_warning = 0

    print("=" * 40)
    print("FAKE AI GENERATION + GRADING RUN")
    print("=" * 40)

    for index, article in enumerate(articles, start=1):
        generated_excerpt = fake_ai(article)
        case_failures, rule_ids = check_excerpt_rules(article, generated_excerpt)

        critical_ids, warning_ids = split_by_severity(rule_ids)
        total_critical += len(critical_ids)
        total_warning += len(warning_ids)

        status = "PASS" if not case_failures else ("CRITICAL" if critical_ids else "WARNING")
        if not case_failures:
            passed_count += 1

        title = article.get("title", f"<untitled #{index}>")
        print(f"\nItem #{index}: {title}  [{status}]")
        print(f"  Generated: {generated_excerpt!r}")
        for reason in case_failures:
            print(f"  - {reason}")

    print("\n" + "=" * 40)
    print(f"SCORE: {passed_count}/{total} generated excerpts passed all rules")
    print(f"{total_critical} critical failure(s), {total_warning} warning(s)")
    print("=" * 40)

    return total_critical == 0


def run_consistency_check(file_path="Articles.json", runs=10, min_pass_rate=0.8):
    with open(file_path, "r", encoding="utf-8") as f:
        articles = json.load(f)

    print("=" * 40)
    print(f"CONSISTENCY CHECK: {runs} runs per article")
    print("=" * 40)

    overall_passed = 0
    overall_total = 0
    overall_critical = 0

    for index, article in enumerate(articles, start=1):
        title = article.get("title", f"<untitled #{index}>")
        article_passed = 0
        article_critical = 0

        for _ in range(runs):
            generated_excerpt = fake_ai(article)
            case_failures, rule_ids = check_excerpt_rules(article, generated_excerpt)
            critical_ids, _ = split_by_severity(rule_ids)

            if not case_failures:
                article_passed += 1
            if critical_ids:
                article_critical += 1

        overall_passed += article_passed
        overall_total += runs
        overall_critical += article_critical

        print(f"\nItem #{index}: {title}")
        print(f"  Passed {article_passed}/{runs} runs"
              + (f"  ({article_critical} had critical failure(s))" if article_critical else ""))

    overall_rate = overall_passed / overall_total if overall_total else 0

    print("\n" + "=" * 40)
    print(f"OVERALL: {overall_passed}/{overall_total} runs passed "
          f"({overall_rate:.0%} pass rate), {overall_critical} run(s) with critical failures")
    print("=" * 40)

    # NEW (Exercise 5): ship-gate. min_pass_rate used to be an unused
    # parameter and this function's return value was ignored by
    # __main__, which always exited 0 ("informational run, not a
    # pass/fail gate"). Now the pass-rate is actually compared against
    # the required threshold, and that comparison drives the exit code.
    gate_passed = overall_rate >= min_pass_rate
    status = "✅ SHIP" if gate_passed else "❌ DO NOT SHIP"
    print(f"\nSHIP GATE: required >= {min_pass_rate:.0%} pass rate -> {status} "
          f"(actual {overall_rate:.0%})")

    return gate_passed


# --------------------------------------------------------------------------
# NEW (Exercise 3): fuzzing. Generates hundreds of random, adversarial
# excerpts and feeds them through check_excerpt_rules — not to check
# exact results, but to check INVARIANTS that must always hold no
# matter the input. Hand-written cases only cover what you thought of;
# fuzzing finds the inputs you didn't.
# --------------------------------------------------------------------------
def generate_random_excerpt():
    """Return one random, possibly-adversarial excerpt string."""
    generators = [
        lambda: "",  # empty string
        lambda: " " * random.randint(0, 20),  # whitespace only
        lambda: "".join(chr(random.randint(32, 126)) for _ in range(random.randint(0, 60))),  # random ASCII
        lambda: "".join(chr(random.randint(0x00, 0x10FFFF)) for _ in range(random.randint(0, 30))
                         if chr(random.randint(0x00, 0x10FFFF)).isprintable() or True),  # random unicode (may include junk)
        lambda: "x" * random.randint(100, 5000),  # huge string
        lambda: random.choice([
            "<script>alert(1)</script>", "< script >alert(1)< /script >",
            "<ScRiPt>bad()</ScRiPt>", "<b>bold</b>", "<div onclick='x()'>",
            "<img src=x onerror=alert(1)>", "no html here at all",
        ]),
        lambda: "".join(random.choice(["🔥", "😀", "🚀", "🎉", "💀", "a", "b", " "]) for _ in range(random.randint(0, 15))),  # emoji mix
        lambda: "Hi" + "\u200b" * random.randint(0, 20),  # invisible padding
        lambda: "text " + "\u202e" + "reversed" + "\u202c",  # RTL override
        lambda: "line1\nline2\ttabbed",  # newline/tab
        lambda: "click ![img](javascript:alert(1))",  # markdown injection
        lambda: "Same Title Same Title",  # potential title-repeat (paired with matching title below)
    ]
    return random.choice(generators)()


def check_invariants(excerpt, item):
    """
    Returns a list of invariant-violation messages (empty if all held).
    These must be true for ANY input, no matter what:
      1. check_excerpt_rules never raises an exception.
      2. An empty string always triggers Rule 1.
      3. A string containing a real <script> tag always triggers Rule 4.
    """
    violations = []

    try:
        case_failures, rule_ids = check_excerpt_rules(item, excerpt)
    except Exception as e:
        violations.append(f"CRASHED: {type(e).__name__}: {e}")
        return violations  # can't check further invariants if it crashed

    if excerpt == "" and "Rule 1" not in rule_ids:
        violations.append("Empty string did not trigger Rule 1")

    if re.search(r"<\s*script\b", excerpt, re.IGNORECASE) and "Rule 4" not in rule_ids:
        violations.append("String containing a <script> tag did not trigger Rule 4")

    return violations


def run_fuzz_test(num_cases=500):
    print("=" * 40)
    print(f"FUZZ TEST: {num_cases} random adversarial inputs")
    print("=" * 40)

    dummy_item = {
        "title": "Same Title Same Title",
        "body": "some body text here"
    }

    total_violations = 0
    crashes = 0

    # Required invariant cases
    guaranteed_cases = [
        "",
        "<script>alert(1)</script>",
        "< script >alert(1)< /script >",
        "<ScRiPt>bad()</ScRiPt>",
    ]

    # Test guaranteed cases first
    for i, excerpt in enumerate(guaranteed_cases):
        violations = check_invariants(excerpt, dummy_item)

        if violations:
            total_violations += len(violations)

            for v in violations:
                if v.startswith("CRASHED"):
                    crashes += 1

                print(f"\n❌ Violation on guaranteed input #{i}:")
                print(f"   Input:     {excerpt!r}")
                print(f"   Violation: {v}")

    # Then test random cases
    for i in range(num_cases):
        excerpt = generate_random_excerpt()
        violations = check_invariants(excerpt, dummy_item)

        if violations:
            total_violations += len(violations)

            for v in violations:
                if v.startswith("CRASHED"):
                    crashes += 1

                print(f"\n❌ Violation on random input #{i}:")
                print(f"   Input:     {excerpt!r}")
                print(f"   Violation: {v}")

    print("\n" + "=" * 40)

    total_cases = num_cases + len(guaranteed_cases)

    if total_violations == 0:
        print(
            f"✅ All {total_cases} inputs held every invariant. "
            f"No bugs found."
        )
    else:
        print(
            f"❌ {total_violations} invariant violation(s) found "
            f"across {total_cases} inputs ({crashes} crashes)."
        )

    print("=" * 40)

    return total_violations == 0


def check_excerpt_rules_with_disabled(item, excerpt, disabled_rule_id):
    """
    Same logic as check_excerpt_rules, except one rule (disabled_rule_id)
    is skipped entirely — as if it didn't exist. Used to simulate
    "what if this rule's check were deleted from the codebase?"
    """
    case_failures = []
    rule_ids = []

    for rule in RULES:
        if rule["id"] == disabled_rule_id:
            continue  # mutation: pretend this rule was never written
        if rule["check"](excerpt, item):
            desc = rule["desc"](excerpt, item) if callable(rule["desc"]) else rule["desc"]
            case_failures.append(f"{rule['id']} Failed: {desc}")
            rule_ids.append(rule["id"])
            if rule.get("stop_on_fail"):
                return case_failures, rule_ids

    return case_failures, rule_ids


# --------------------------------------------------------------------------
# NEW (Exercise 4): mutation testing. For each rule, disable it and
# re-run the self-check against Articles.json. If NO case's actual
# detections diverge from its expected_failures because of that rule
# specifically, then no test case was actually guarding that rule —
# it could be deleted from the codebase and nobody would notice.
# --------------------------------------------------------------------------
def run_mutation_test(file_path="Articles.json"):
    with open(file_path, "r", encoding="utf-8") as f:
        cases = json.load(f)

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

            excerpt = item.get("excerpt")
            expected_ids = set(item["expected_failures"])

            try:
                _, mutated_rule_ids = check_excerpt_rules_with_disabled(item, excerpt, rule_id)
                actual_ids = set(mutated_rule_ids)
            except Exception:
                # Disabling this rule broke something downstream (e.g.
                # Additional Rule A normally stops non-string excerpts
                # before .strip() runs). A crash here is proof this
                # rule was load-bearing, not a mutation-test failure.
                caught_by_at_least_one_case = True
                break

            # Did disabling this rule cause a mismatch it wouldn't have
            # had otherwise? Only meaningful if this rule was actually
            # expected to fire for this case.
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


if __name__ == "__main__":
    file_path = sys.argv[1] if len(sys.argv) > 1 else "tests/AiTesting/Articles.json"

    min_pass_rate = 0.8
    if "--min-pass-rate" in sys.argv:
        idx = sys.argv.index("--min-pass-rate")
        if idx + 1 < len(sys.argv):
            min_pass_rate = float(sys.argv[idx + 1])

    if len(sys.argv) > 2 and sys.argv[2] == "--fake-ai":
        ok = run_fake_ai_pipeline(file_path)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--consistency":
        ok = run_consistency_check(file_path, min_pass_rate=min_pass_rate)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 1 and sys.argv[1] == "--fuzz":
        num_cases = int(sys.argv[2]) if len(sys.argv) > 2 else 500
        ok = run_fuzz_test(num_cases)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--mutation":
        ok = run_mutation_test(file_path)
        sys.exit(0 if ok else 1)
    else:
        ok = grade_excerpts(file_path)
        sys.exit(0 if ok else 1)
