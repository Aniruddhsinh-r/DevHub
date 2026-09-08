"""
Shared rule-checking engine.

A domain (e.g. "comments", "excerpts") plugs into this engine by supplying:
  - RULES: a list of rule dicts {id, severity, desc, check, [stop_on_fail]}
  - the JSON field name holding the value to validate
  - small domain-specific helpers for fuzz/metamorphic testing (generators,
    invariant checks, truncation, etc.)

Nothing in this file knows what a "comment" or an "excerpt" is - it only
walks whatever RULES list it is given.
"""

import json
import time
import sys

if sys.stdout.encoding.lower() != "utf-8":
    sys.stdout.reconfigure(encoding="utf-8")

# ---------------------------------------------------------------------------
# Core rule checking
# ---------------------------------------------------------------------------

def check_rules(rules, item, value, disabled_rule_id=None):
    """Evaluate `value` (and its parent `item`) against `rules`.

    If `disabled_rule_id` is given, that rule is skipped (used by the
    mutation test to simulate a rule being removed).

    Returns (failure_messages, rule_ids).
    """
    failures = []
    rule_ids = []
    for rule in rules:
        if disabled_rule_id is not None and rule["id"] == disabled_rule_id:
            continue
        if rule["check"](value, item):
            desc = rule["desc"](value, item) if callable(rule["desc"]) else rule["desc"]
            failures.append(f"{rule['id']} Failed: {desc}")
            rule_ids.append(rule["id"])
            if rule.get("stop_on_fail"):
                break
    return failures, rule_ids


def rule_severity_map(rules):
    return {rule["id"]: rule["severity"] for rule in rules}


def split_by_severity(rule_ids, severity_map):
    critical = [r for r in rule_ids if severity_map.get(r) == "critical"]
    warning = [r for r in rule_ids if severity_map.get(r) == "warning"]
    return critical, warning


def load_cases(file_path):
    """Load a JSON list of cases. Returns (cases, error_message).
    Exactly one of the two return values is not None/empty."""
    try:
        with open(file_path, "r", encoding="utf-8") as f:
            cases = json.load(f)
    except Exception as e:
        return None, f"Error reading '{file_path}': {e}"
    if not isinstance(cases, list):
        return None, f"Invalid JSON format: expected a list of objects, got {type(cases).__name__}."
    return cases, None


# ---------------------------------------------------------------------------
# Reporting
# ---------------------------------------------------------------------------

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


# ---------------------------------------------------------------------------
# Grading (the "does every case match its expected_failures" run)
# ---------------------------------------------------------------------------

def grade_cases(file_path, rules, value_field, header_label, title_fn, show_severity_counts=False):
    """Generic grading loop. `title_fn(item, index) -> str` builds the label
    used in reports for a given case."""
    cases, err = load_cases(file_path)
    if err:
        print(err)
        return False

    total = len(cases)
    passed_count = 0
    failures = []
    correct_count = 0
    no_expectation = []
    mismatches = []
    total_critical = 0
    total_warning = 0
    severity_map = rule_severity_map(rules)
    rule_stats = {rule["id"]: {"expected": 0, "tp": 0, "fp": 0} for rule in rules}

    for index, item in enumerate(cases, start=1):
        value = item.get(value_field)
        case_failures, rule_ids = check_rules(rules, item, value)

        if show_severity_counts:
            critical_ids, warning_ids = split_by_severity(rule_ids, severity_map)
            total_critical += len(critical_ids)
            total_warning += len(warning_ids)

        if not case_failures:
            passed_count += 1
        else:
            failures.append((index, case_failures))

        title = title_fn(item, index)
        if "expected_failures" not in item:
            no_expectation.append((index, title))
        else:
            expected_ids = set(item["expected_failures"])
            actual_ids = set(rule_ids)
            if actual_ids == expected_ids:
                correct_count += 1
            else:
                missed = sorted(expected_ids - actual_ids)
                surprise = sorted(actual_ids - expected_ids)
                mismatches.append((index, title, missed, surprise))

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
    print(f"{header_label}: Passed {passed_count}/{total}")
    if show_severity_counts:
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


# ---------------------------------------------------------------------------
# Mutation testing
# ---------------------------------------------------------------------------

def run_mutation_test(file_path, rules, value_field):
    cases, err = load_cases(file_path)
    if err:
        print(err)
        return False

    print("=" * 40)
    print("MUTATION TEST: disabling each rule, one at a time")
    print("=" * 40)

    untested_rules = []

    for rule in rules:
        rule_id = rule["id"]
        caught_by_at_least_one_case = False

        for item in cases:
            if "expected_failures" not in item:
                continue

            value = item.get(value_field)
            expected_ids = set(item["expected_failures"])

            try:
                _, mutated_rule_ids = check_rules(rules, item, value, disabled_rule_id=rule_id)
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


# ---------------------------------------------------------------------------
# Fuzz testing
# ---------------------------------------------------------------------------

def run_fuzz_test(rules, generate_value, check_invariants, dummy_item, guaranteed_cases, num_cases=500):
    """`check_invariants(rules, value, item) -> [violation strings]` is
    supplied by the domain."""
    print("=" * 40)
    print(f"FUZZ TEST: {num_cases} random adversarial inputs")
    print("=" * 40)

    total_violations = 0
    crashes = 0

    def _run_one(label, value):
        nonlocal total_violations, crashes
        violations = check_invariants(rules, value, dummy_item)
        if violations:
            total_violations += len(violations)
            for v in violations:
                if v.startswith("CRASHED"):
                    crashes += 1
                print(f"\n❌ Violation on {label}:")
                print(f"   Input:     {value!r}")
                print(f"   Violation: {v}")

    for i, value in enumerate(guaranteed_cases):
        _run_one(f"guaranteed input #{i}", value)

    for i in range(num_cases):
        _run_one(f"random input #{i}", generate_value())

    print("\n" + "=" * 40)
    total_cases = num_cases + len(guaranteed_cases)
    if total_violations == 0:
        print(f"✅ All {total_cases} inputs held every invariant. No bugs found.")
    else:
        print(f"❌ {total_violations} invariant violation(s) found across {total_cases} inputs ({crashes} crashes).")
    print("=" * 40)

    return total_violations == 0


# ---------------------------------------------------------------------------
# Metamorphic testing
# ---------------------------------------------------------------------------

def run_metamorphic_test(rules, corpus, truncate_fn, truncation_trigger_rule,
                          whitespace_ignore_rules, monotonic_rules, extend_text):
    print("=" * 40)
    print("METAMORPHIC TEST: checking relationships between outputs")
    print("=" * 40)

    stats = {
        "determinism": {"tested": 0, "violations": []},
        "truncation": {"tested": 0, "violations": []},
        "whitespace": {"tested": 0, "violations": []},
        "superstring": {"tested": 0, "violations": []},
    }

    for item, value in corpus:
        if not isinstance(value, str):
            continue

        try:
            _, base_rule_ids = check_rules(rules, item, value)
        except Exception as e:
            stats["determinism"]["violations"].append(
                (value, None, f"CRASHED on base value: {type(e).__name__}: {e}")
            )
            continue
        base_set = set(base_rule_ids)

        # --- Relation 1: determinism ---
        stats["determinism"]["tested"] += 1
        _, repeat_rule_ids = check_rules(rules, item, value)
        if set(repeat_rule_ids) != base_set:
            stats["determinism"]["violations"].append(
                (value, None, f"first={sorted(base_set)} second={sorted(repeat_rule_ids)}")
            )

        # --- Relation 2: truncation ---
        if truncation_trigger_rule in base_set:
            stats["truncation"]["tested"] += 1
            truncated = truncate_fn(value)
            _, trunc_rule_ids = check_rules(rules, item, truncated)
            trunc_set = set(trunc_rule_ids)
            new_rules = trunc_set - base_set
            if truncation_trigger_rule in trunc_set or new_rules:
                stats["truncation"]["violations"].append(
                    (value, truncated,
                     f"before={sorted(base_set)} after={sorted(trunc_set)} "
                     f"({truncation_trigger_rule} still present: {truncation_trigger_rule in trunc_set}, "
                     f"new rules: {sorted(new_rules)})")
                )

        # --- Relation 3: whitespace ---
        for pad in (1, 3, 10):
            stats["whitespace"]["tested"] += 1
            padded = value + (" " * pad)
            _, pad_rule_ids = check_rules(rules, item, padded)
            pad_set = set(pad_rule_ids)

            diff = pad_set.symmetric_difference(base_set)
            if diff - whitespace_ignore_rules:
                stats["whitespace"]["violations"].append(
                    (value, padded, f"before={sorted(base_set)} after(+{pad} spaces)={sorted(pad_set)}")
                )

        # --- Relation 4: superstring monotonicity ---
        monotonic_hit = base_set & monotonic_rules
        if monotonic_hit:
            stats["superstring"]["tested"] += 1
            extended = value + extend_text
            _, ext_rule_ids = check_rules(rules, item, extended)
            ext_set = set(ext_rule_ids)
            vanished = monotonic_hit - ext_set
            if vanished:
                stats["superstring"]["violations"].append(
                    (value, extended, f"rules {sorted(vanished)} disappeared after appending plain text")
                )

    total_violations = sum(len(s["violations"]) for s in stats.values())

    for name, label in [
        ("determinism", "Determinism (same value graded twice)"),
        ("truncation", "Truncation (cutting under the length limit drops the length rule, nothing new)"),
        ("whitespace", "Whitespace (trailing spaces don't change verdict)"),
        ("superstring", "Superstring monotonicity (rules can't vanish by appending text)"),
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


# ---------------------------------------------------------------------------
# Fake-AI pipeline / consistency ship-gate (used by domains that generate content)
# ---------------------------------------------------------------------------

def run_fake_ai_pipeline(file_path, rules, fake_ai_fn):
    cases, err = load_cases(file_path)
    if err:
        print(err)
        return False

    total = len(cases)
    passed_count = 0
    total_critical = 0
    total_warning = 0
    severity_map = rule_severity_map(rules)

    print("=" * 40)
    print("FAKE AI GENERATION + GRADING RUN")
    print("=" * 40)

    for index, article in enumerate(cases, start=1):
        generated = fake_ai_fn(article)
        case_failures, rule_ids = check_rules(rules, article, generated)

        critical_ids, warning_ids = split_by_severity(rule_ids, severity_map)
        total_critical += len(critical_ids)
        total_warning += len(warning_ids)

        status = "PASS" if not case_failures else ("CRITICAL" if critical_ids else "WARNING")
        if not case_failures:
            passed_count += 1

        title = article.get("title", f"<untitled #{index}>")
        print(f"\nItem #{index}: {title}  [{status}]")
        print(f"  Generated: {generated!r}")
        for reason in case_failures:
            print(f"  - {reason}")

    print("\n" + "=" * 40)
    print(f"SCORE: {passed_count}/{total} generated excerpts passed all rules")
    print(f"{total_critical} critical failure(s), {total_warning} warning(s)")
    print("=" * 40)

    return total_critical == 0


def run_consistency_check(file_path, rules, fake_ai_fn, runs=10, min_pass_rate=0.8):
    cases, err = load_cases(file_path)
    if err:
        print(err)
        return False

    print("=" * 40)
    print(f"CONSISTENCY CHECK: {runs} runs per article")
    print("=" * 40)

    overall_passed = 0
    overall_total = 0
    overall_critical = 0
    severity_map = rule_severity_map(rules)

    for index, article in enumerate(cases, start=1):
        title = article.get("title", f"<untitled #{index}>")
        article_passed = 0
        article_critical = 0

        for _ in range(runs):
            generated = fake_ai_fn(article)
            case_failures, rule_ids = check_rules(rules, article, generated)
            critical_ids, _ = split_by_severity(rule_ids, severity_map)

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


# ---------------------------------------------------------------------------
# Performance / ReDoS smoke test
# ---------------------------------------------------------------------------

def measure_rule_performance(rules, dummy_item, nasty_inputs):
    print("=" * 40)
    print("FULL GRADER PERFORMANCE TEST")
    print("Testing Multiple Nasty 1M-Character Inputs")
    print("=" * 40)

    all_results = []

    for input_name, malicious_input in nasty_inputs:
        print(f"\n📝 Testing: {input_name}")
        print(f"   Length: {len(malicious_input):,} characters")
        print("-" * 40)

        results = []
        for rule in rules:
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

        if results:
            slowest = max(results, key=lambda x: x[1])
            print(f"\n   🐢 Slowest: {slowest[0]} took {slowest[1]:.6f}s")
            all_results.append((input_name, slowest[0], slowest[1]))

    print("\n" + "=" * 40)
    print("SUMMARY: Slowest Rule for Each Input")
    print("=" * 40)
    for input_name, rule_id, time_taken in all_results:
        print(f"{input_name:<35} -> {rule_id:<15} ({time_taken:.6f}s)")

    if all_results:
        overall_slowest = max(all_results, key=lambda x: x[2])
        print("\n" + "=" * 40)
        print(f"🏆 OVERALL SLOWEST: {overall_slowest[1]} on '{overall_slowest[0]}'")
        print(f"   Time: {overall_slowest[2]:.6f} seconds")
        print("=" * 40)

        if overall_slowest[2] > 1.0:
            print(f"\n⚠️ WARNING: {overall_slowest[1]} took > 1 second!")
            print("   This could be a ReDoS vulnerability!")
            return False
        else:
            print(f"\n✅ All inputs processed in under 1 second!")
            print("   No ReDoS vulnerabilities found.")
            return True

    return False


# ---------------------------------------------------------------------------
# Mutation testing - Enhanced with both types
# ---------------------------------------------------------------------------

def run_mutation_test(file_path, rules, value_field):
    """Run both types of mutation tests:
    1. Disable each rule - checks if rule is tested (caught when missing)
    2. Force each rule to return True - checks if rule falsely triggers
    """
    cases, err = load_cases(file_path)
    if err:
        print(err)
        return False

    print("=" * 40)
    print("MUTATION TEST: two types per rule")
    print("=" * 40)

    untested_rules = []      # Type 1: disabling changes nothing
    untested_surprise = []   # Type 2: forcing true causes no surprise failures
    all_passed = True

    for rule in rules:
        rule_id = rule["id"]

        # --- TYPE 1: DISABLE mutation ---
        # Disable the rule and see if any case that expected it now misses it
        caught_by_at_least_one_case = False
        original_check = rule["check"]

        for item in cases:
            if "expected_failures" not in item:
                continue

            value = item.get(value_field)
            expected_ids = set(item["expected_failures"])

            try:
                _, mutated_rule_ids = check_rules(rules, item, value, disabled_rule_id=rule_id)
                actual_ids = set(mutated_rule_ids)
            except Exception:
                caught_by_at_least_one_case = True
                break

            if rule_id in expected_ids and rule_id not in actual_ids:
                caught_by_at_least_one_case = True
                break

        if not caught_by_at_least_one_case:
            untested_rules.append(rule_id)

        # --- TYPE 2: FORCE-TRUE mutation ---
        # Force the rule to return True always and see if any case gets a surprise failure
        force_true_caught = False
        force_true_surprises = []

        # Temporarily override the rule's check to always return True
        def make_always_true(original_check):
            def always_true(*args, **kwargs):
                return True
            return always_true

        # Store original and override
        rule["check"] = make_always_true(original_check)

        for item in cases:
            if "expected_failures" not in item:
                continue

            value = item.get(value_field)
            expected_ids = set(item["expected_failures"])

            try:
                _, mutated_rule_ids = check_rules(rules, item, value)
                actual_ids = set(mutated_rule_ids)
            except Exception:
                force_true_caught = True
                force_true_surprises.append("CRASHED")
                break

            # If the rule fired (it always will) but wasn't expected to, that's a surprise
            if rule_id in actual_ids and rule_id not in expected_ids:
                force_true_caught = True
                force_true_surprises.append(item)
                # Only need to find one, but let's collect first few for reporting
                if len(force_true_surprises) >= 3:
                    break

        # Restore original check
        rule["check"] = original_check

        if not force_true_caught:
            untested_surprise.append(rule_id)

        # Print status for this rule
        disable_status = "✅ guarded" if caught_by_at_least_one_case else "❌ NO TEST CATCHES THIS"
        force_status = "✅ guarded" if force_true_caught else "❌ NO SURPRISE DETECTED"
        print(f"  {rule_id:<20} DISABLE: {disable_status:<30} FORCE-TRUE: {force_status}")

    print("\n" + "=" * 40)

    if untested_rules:
        print(f"❌ {len(untested_rules)} rule(s) have zero test coverage (disabling changes nothing):")
        print(f"   {untested_rules}")
        print("   Add a case whose expected_failures includes each rule id")
        all_passed = False
    else:
        print("✅ Every rule is tested (disabling it changes at least one case).")

    if untested_surprise:
        print(f"\n❌ {len(untested_surprise)} rule(s) have no 'stay quiet' test (forcing true causes no surprise):")
        print(f"   {untested_surprise}")
        print("   Add a case that does NOT expect this rule to fire (proves it stays quiet on good input)")
        all_passed = False
    else:
        print("✅ Every rule has a 'stay quiet' test (forcing it true causes surprise failures).")

    print("=" * 40)

    return all_passed
