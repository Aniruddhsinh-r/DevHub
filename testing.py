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

RULE_SEVERITY = {
    "Additional Rule A": "critical",
    "Rule 1": "warning",
    "Rule 2": "warning",
    "Rule 3": "warning",
    "Rule 4": "critical",
    "Rule 5": "warning",
    "Rule 6": "critical",
    "Rule 7": "critical",
    "Rule 8": "warning",
    "Rule 9": "critical",
    "Rule 10": "warning",
    "Rule 11": "warning",
}


def clean_invisible(text):
    """Strip invisible/zero-width characters, collapsing them to a space."""
    return INVISIBLE_CHARS.sub(' ', text).strip()


def check_excerpt_rules(item, excerpt):
    case_failures = []
    rule_ids = []

    if excerpt is None or not isinstance(excerpt, str):
        case_failures.append("Excerpt field is missing or not a string")
        rule_ids.append("Additional Rule A")
        return case_failures, rule_ids

    if len(excerpt.strip()) == 0:
        case_failures.append("Rule 1 Failed: Excerpt is empty")
        rule_ids.append("Rule 1")

    cleaned = clean_invisible(excerpt)
    if len(cleaned) <= 6:
        case_failures.append(f"Rule 2 Failed: Length ({len(cleaned)}) is 6 or fewer characters")
        rule_ids.append("Rule 2")

    if len(cleaned) > 50:
        case_failures.append(f"Rule 3 Failed: Length ({len(cleaned)}) exceeds 50 characters")
        rule_ids.append("Rule 3")

    if re.search(r"<[^>]+>", excerpt):
        case_failures.append(f"Rule 4 Failed: Contains forbidden HTML tags")
        rule_ids.append("Rule 4")

    body = item.get("body", "")
    if body and excerpt.strip() == body.strip():
        case_failures.append(f"Rule 5 Failed: Excerpt is identical to the full body (not a summary)")
        rule_ids.append("Rule 5")

    if len(excerpt) > 0:
        invisible_count = len(INVISIBLE_CHARS.findall(excerpt))
        if invisible_count / len(excerpt) > 0.3:
            case_failures.append(
                f"Rule 6 Failed: Excerpt is mostly invisible characters "
                f"({invisible_count}/{len(excerpt)})"
            )
            rule_ids.append("Rule 6")

    if DIRECTIONAL_OVERRIDES.search(excerpt):
        case_failures.append(f"Rule 7 Failed: Contains RTL/LTR directional override characters")
        rule_ids.append("Rule 7")

    if re.search(r'[\n\r\t]', excerpt):
        case_failures.append(f"Rule 8 Failed: Contains embedded newline/tab characters")
        rule_ids.append("Rule 8")

    if MARKDOWN_INJECTION.search(excerpt):
        case_failures.append(f"Rule 9 Failed: Contains markdown link/image injection")
        rule_ids.append("Rule 9")

    text_no_emoji = EMOJI_PATTERN.sub('', excerpt).strip()
    if excerpt.strip() and not text_no_emoji:
        case_failures.append(f"Rule 10 Failed: Excerpt is emoji-only, no real text")
        rule_ids.append("Rule 10")

    title = item.get("title", "")
    if title and excerpt.strip().lower() == title.strip().lower():
        case_failures.append(f"Rule 11 Failed: Excerpt is identical to the title")
        rule_ids.append("Rule 11")

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


def grade_excerpts(file_path="tests/AiTesting/Articles.json"):
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

    return total_critical == 0 and not mismatches and not no_expectation


def run_fake_ai_pipeline(file_path="tests/AiTesting/Articles.json"):
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


# --------------------------------------------------------------------------
# NEW: consistency check — run fake_ai N times per article, since a
# non-deterministic generator can't be judged from a single call. Reports
# a pass-rate per article ("Passed 7/10 runs") plus an overall rate
# across every article and every run.
# --------------------------------------------------------------------------
def run_consistency_check(file_path="tests/AiTesting/Articles.json", runs=10):
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

    return overall_rate


if __name__ == "__main__":
    file_path = sys.argv[1] if len(sys.argv) > 1 else "tests/AiTesting/Articles.json"

    if len(sys.argv) > 2 and sys.argv[2] == "--fake-ai":
        ok = run_fake_ai_pipeline(file_path)
        sys.exit(0 if ok else 1)
    elif len(sys.argv) > 2 and sys.argv[2] == "--consistency":
        run_consistency_check(file_path)
        sys.exit(0)  # informational run, not a pass/fail gate
    else:
        ok = grade_excerpts(file_path)
        sys.exit(0 if ok else 1)
