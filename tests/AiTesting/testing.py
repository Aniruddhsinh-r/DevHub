import json
import random
import re
import sys
import emoji
from collections import Counter

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
            f"Length ({len(clean_invisible(excerpt))}) exceeds 50 characters"
        ),
        "check": lambda excerpt, item: len(clean_invisible(excerpt)) > 50,
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
]

RULE_SEVERITY = {rule["id"]: rule["severity"] for rule in RULES}


def check_excerpt_rules(item, excerpt):
    case_failures = []
    rule_ids = []
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

    gate_passed = overall_rate >= min_pass_rate
    status = "✅ SHIP" if gate_passed else "❌ DO NOT SHIP"
    print(f"\nSHIP GATE: required >= {min_pass_rate:.0%} pass rate -> {status} "
          f"(actual {overall_rate:.0%})")

    return gate_passed


def generate_random_excerpt():
    """Return one random, possibly-adversarial excerpt string."""
    generators = [
        lambda: "",
        lambda: " " * random.randint(0, 20),
        lambda: "".join(chr(random.randint(32, 126)) for _ in range(random.randint(0, 60))),
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


def check_invariants(excerpt, item):
    violations = []
    try:
        case_failures, rule_ids = check_excerpt_rules(item, excerpt)
    except Exception as e:
        violations.append(f"CRASHED: {type(e).__name__}: {e}")
        return violations

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

    guaranteed_cases = [
        "",
        "<script>alert(1)</script>",
        "< script >alert(1)< /script >",
        "<ScRiPt>bad()</ScRiPt>",
    ]

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
        print(f"✅ All {total_cases} inputs held every invariant. No bugs found.")
    else:
        print(f"❌ {total_violations} invariant violation(s) found across {total_cases} inputs ({crashes} crashes).")
    print("=" * 40)

    return total_violations == 0


def check_excerpt_rules_with_disabled(item, excerpt, disabled_rule_id):
    case_failures = []
    rule_ids = []
    for rule in RULES:
        if rule["id"] == disabled_rule_id:
            continue
        if rule["check"](excerpt, item):
            desc = rule["desc"](excerpt, item) if callable(rule["desc"]) else rule["desc"]
            case_failures.append(f"{rule['id']} Failed: {desc}")
            rule_ids.append(rule["id"])
            if rule.get("stop_on_fail"):
                return case_failures, rule_ids
    return case_failures, rule_ids


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


def _truncate_under_50(excerpt):
    """Cut an excerpt so clean_invisible(result) is under 50 chars,
    trimming at the last word boundary so truncation doesn't itself
    accidentally trip Rule 2 (too few words) as a side effect."""
    cleaned = clean_invisible(excerpt)
    truncated = cleaned[:45]
    if ' ' in truncated:
        truncated = truncated[:truncated.rfind(' ')]
    return truncated


def _metamorphic_corpus(file_path):
    """Base cases to run every relation over: real excerpts from the file,
    hand-picked seeds guaranteed to satisfy each relation's precondition
    (so the relation actually gets exercised, not skipped), plus random
    fuzzed strings for breadth."""
    corpus = []

    try:
        with open(file_path, "r", encoding="utf-8") as f:
            cases = json.load(f)
        for item in cases:
            excerpt = item.get("excerpt")
            if isinstance(excerpt, str) and excerpt != "":
                corpus.append((item, excerpt))
    except Exception:
        pass

    dummy_item = {"title": "Same Title Same Title", "body": "some body text here"}

    seeds = [
        "x" * 80,
        "This sentence is intentionally a bit longer than fifty characters total.",
        "<b>bold html tag that is long enough to also be too long for rule three</b>",
        "text " + "\u202e" + "reversed override that also runs long enough to trip length" + "\u202c",
        "see [2020](https://ex.com/preprint) markdown injection padded out long enough",
        "no problems here just a normal short excerpt",
    ]
    for s in seeds:
        corpus.append((dummy_item, s))

    for _ in range(200):
        corpus.append((dummy_item, generate_random_excerpt()))

    return corpus


def run_metamorphic_test(file_path="Articles.json"):
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

    for item, excerpt in corpus:
        if not isinstance(excerpt, str):
            continue

        try:
            _, base_rule_ids = check_excerpt_rules(item, excerpt)
        except Exception as e:
            stats["determinism"]["violations"].append(
                (excerpt, None, f"CRASHED on base excerpt: {type(e).__name__}: {e}")
            )
            continue
        base_set = set(base_rule_ids)

        # --- Relation 1: determinism ---
        stats["determinism"]["tested"] += 1
        _, repeat_rule_ids = check_excerpt_rules(item, excerpt)
        if set(repeat_rule_ids) != base_set:
            stats["determinism"]["violations"].append(
                (excerpt, None, f"first={sorted(base_set)} second={sorted(repeat_rule_ids)}")
            )

        # --- Relation 2: truncation ---
        if "Rule 3" in base_set:
            stats["truncation"]["tested"] += 1
            truncated = _truncate_under_50(excerpt)
            _, trunc_rule_ids = check_excerpt_rules(item, truncated)
            trunc_set = set(trunc_rule_ids)
            new_rules = trunc_set - base_set
            if "Rule 3" in trunc_set or new_rules:
                stats["truncation"]["violations"].append(
                    (excerpt, truncated,
                     f"before={sorted(base_set)} after={sorted(trunc_set)} "
                     f"(Rule 3 still present: {'Rule 3' in trunc_set}, new rules: {sorted(new_rules)})")
                )

        # --- Relation 3: whitespace ---
        for pad in (1, 3, 10):
            stats["whitespace"]["tested"] += 1
            padded = excerpt + (" " * pad)
            _, pad_rule_ids = check_excerpt_rules(item, padded)
            pad_set = set(pad_rule_ids)
            if pad_set != base_set:
                stats["whitespace"]["violations"].append(
                    (excerpt, padded,
                     f"before={sorted(base_set)} after(+{pad} spaces)={sorted(pad_set)}")
                )

        # --- Relation 4 (invented): superstring monotonicity ---
        monotonic_rules = base_set & {"Rule 4", "Rule 7", "Rule 9"}
        if monotonic_rules:
            stats["superstring"]["tested"] += 1
            extended = excerpt + " and here is some extra plain text appended after it"
            _, ext_rule_ids = check_excerpt_rules(item, extended)
            ext_set = set(ext_rule_ids)
            vanished = monotonic_rules - ext_set
            if vanished:
                stats["superstring"]["violations"].append(
                    (excerpt, extended,
                     f"rules {sorted(vanished)} disappeared after appending plain text")
                )

    total_violations = sum(len(s["violations"]) for s in stats.values())

    for name, label in [
        ("determinism", "Determinism (same excerpt graded twice)"),
        ("truncation", "Truncation (cut under 50 chars drops Rule 3, nothing new)"),
        ("whitespace", "Whitespace (trailing spaces don't change verdict)"),
        ("superstring", "Superstring monotonicity (Rule 4/7/9 can't vanish by appending text)"),
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


def test_patterns_performance():
    """Test the full grader with multiple nasty 1-million-character inputs"""
    import time

    print("=" * 40)
    print("FULL GRADER PERFORMANCE TEST")
    print("Testing Multiple Nasty 1M-Character Inputs")
    print("=" * 40)

    # Define nasty test inputs
    nasty_inputs = [
        ("Thousands of < with no >", "<" * 1000000),
        ("Deeply repeated pattern", "ab" * 1000),
        ("Huge runs of invisible chars", "\u200b" * 50),
        ("Nested brackets", "(" * 500000 + ")" * 50),
        ("Mixed HTML-like", "<div " * 250000 + ">" * 250000),
        ("Long repeated word", "test " * 200000),
    ]

    dummy_item = {
        "title": "Performance Test",
        "body": "Test body",
        "expected_failures": ["Rule 3"]
    }

    all_results = []

    for input_name, malicious_input in nasty_inputs:
        print(f"\n📝 Testing: {input_name}")
        print(f"   Length: {len(malicious_input):,} characters")
        print("-" * 40)

        results = []
        for rule in RULES:
            rule_id = rule["id"]
            start = time.time()
            try:
                result = rule["check"](malicious_input, dummy_item)
                elapsed = time.time() - start
                results.append((rule_id, elapsed, result))

                status = "⚠️ SLOW" if elapsed > 1.0 else "✅"
                print(f"{rule_id:<20} {elapsed:.6f}s  {status}  (fired: {result})")
            except Exception as e:
                elapsed = time.time() - start
                print(f"{rule_id:<20} {elapsed:.6f}s  ❌ ERROR: {str(e)[:50]}")
                results.append((rule_id, elapsed, None))

        # Find slowest for this input
        if results:
            slowest = max(results, key=lambda x: x[1])
            print(f"\n   🐢 Slowest: {slowest[0]} took {slowest[1]:.6f}s")
            all_results.append((input_name, slowest[0], slowest[1]))

    # Summary - find overall slowest across all inputs
    print("\n" + "=" * 40)
    print("SUMMARY: Slowest Rule for Each Input")
    print("=" * 40)

    for input_name, rule_id, time_taken in all_results:
        print(f"{input_name:<35} -> {rule_id:<15} ({time_taken:.6f}s)")

    # Find the absolute slowest
    if all_results:
        overall_slowest = max(all_results, key=lambda x: x[2])
        print("\n" + "=" * 40)
        print(f"🏆 OVERALL SLOWEST: {overall_slowest[1]} on '{overall_slowest[0]}'")
        print(f"   Time: {overall_slowest[2]:.6f} seconds")
        print("=" * 40)

        # Check if any exceeded 1 second
        if overall_slowest[2] > 1.0:
            print(f"\n⚠️ WARNING: {overall_slowest[1]} took > 1 second!")
            print("   This could be a ReDoS vulnerability!")
            return False
        else:
            print(f"\n✅ All inputs processed in under 1 second!")
            print("   No ReDoS vulnerabilities found.")
            return True

    return False


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
    elif len(sys.argv) > 2 and sys.argv[2] == "--metamorphic":
        ok = run_metamorphic_test(file_path)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 1 and sys.argv[1] == "--pattern":
        ok = test_patterns_performance()
        sys.exit(0 if ok else 1)
    else:
        ok = grade_excerpts(file_path)
        sys.exit(0 if ok else 1)
