import json
import re


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


def grade_excerpts(file_path="tests/AiTesting/Articles.json"):
    try:
        with open(file_path, "r", encoding="utf-8") as f:
            cases = json.load(f)
    except Exception as e:
        print(f"FAILED RULE 5: Invalid JSON format in '{file_path}'. Error: {e}")
        return

    if not isinstance(cases, list):
        print(f"FAILED RULE 5: Root JSON must be a list of cases, got {type(cases).__name__}.")
        return

    total = len(cases)
    passed_count = 0
    failures = []

    # --- self-check bookkeeping ---
    correct_count = 0
    no_expectation = []
    mismatches = []

    for index, item in enumerate(cases, start=1):
        excerpt = item.get("excerpt")
        case_failures = []   # printable strings (unchanged, for the existing report)
        rule_ids = []         # rule IDs only (new, for self-check comparison)

        # Additional Rule A: Check if excerpt field exists and is a string
        if excerpt is None or not isinstance(excerpt, str):
            case_failures.append("Excerpt field is missing or not a string")
            rule_ids.append("Additional Rule A")
        else:
            if len(excerpt.strip()) == 0:
                case_failures.append("Rule 1 Failed: Excerpt is empty")
                rule_ids.append("Rule 1")

            # Rule 2: Excerpt must have more than 6 characters (invisible-char safe)
            cleaned = clean_invisible(excerpt)
            if len(cleaned) <= 6:
                case_failures.append(f"Rule 2 Failed: Length ({len(cleaned)}) is 6 or fewer characters")
                rule_ids.append("Rule 2")

            # Rule 3: Excerpt not longer than 50 characters (with spaces)
            if len(cleaned) > 50:
                case_failures.append(f"Rule 3 Failed: Length ({len(cleaned)}) exceeds 50 characters")
                rule_ids.append("Rule 3")

            # Rule 4: Must not contain any HTML tags (e.g., <b>, <p>, <br/>)
            if re.search(r"<[^>]+>", excerpt):
                case_failures.append(f"Rule 4 Failed: Contains forbidden HTML tags")
                rule_ids.append("Rule 4")

            # Additional Rule B: Check if excerpt is identical to the full body
            body = item.get("body", "")
            if body and excerpt.strip() == body.strip():
                case_failures.append(f"Rule 5 Failed: Excerpt is identical to the full body (not a summary)")
                rule_ids.append("Rule 5")

            # Additional Rule C: Excessive invisible-character padding (>30% of raw length)
            if len(excerpt) > 0:
                invisible_count = len(INVISIBLE_CHARS.findall(excerpt))
                if invisible_count / len(excerpt) > 0.3:
                    case_failures.append(
                        f"Rule 6 Failed: Excerpt is mostly invisible characters "
                        f"({invisible_count}/{len(excerpt)})"
                    )
                    rule_ids.append("Rule 6")

            # Additional Rule D: Contains directional override characters (RTL/LTR spoofing)
            if DIRECTIONAL_OVERRIDES.search(excerpt):
                case_failures.append(f"Rule 7 Failed: Contains RTL/LTR directional override characters")
                rule_ids.append("Rule 7")

            # Additional Rule E: Contains raw newlines or tabs (layout-breaking)
            if re.search(r'[\n\r\t]', excerpt):
                case_failures.append(f"Rule 8 Failed: Contains embedded newline/tab characters")
                rule_ids.append("Rule 8")

            # Additional Rule F: Contains markdown link/image injection
            if MARKDOWN_INJECTION.search(excerpt):
                case_failures.append(f"Rule 9 Failed: Contains markdown link/image injection")
                rule_ids.append("Rule 9")

            # Additional Rule G: Excerpt is emoji-only (no real text content)
            text_no_emoji = EMOJI_PATTERN.sub('', excerpt).strip()
            if excerpt.strip() and not text_no_emoji:
                case_failures.append(f"Rule 10 Failed: Excerpt is emoji-only, no real text")
                rule_ids.append("Rule 10")

            # Additional Rule H: Excerpt is just the title repeated (low-effort/SEO stuffing)
            title = item.get("title", "")
            if title and excerpt.strip().lower() == title.strip().lower():
                case_failures.append(f"Rule 11 Failed: Excerpt is identical to the title")
                rule_ids.append("Rule 11")

        if not case_failures:
            passed_count += 1
        else:
            failures.append((index, case_failures))

        # --- self-check: compare actual rule_ids vs expected_failures ---
        title_for_report = item.get("title", f"<untitled #{index}>")
        if "expected_failures" not in item:
            no_expectation.append((index, title_for_report))
        else:
            expected_ids = set(item["expected_failures"])
            actual_ids = set(rule_ids)
            if actual_ids == expected_ids:
                correct_count += 1
            else:
                missed = sorted(expected_ids - actual_ids)   # expected to fail, but passed
                surprise = sorted(actual_ids - expected_ids)  # failed, but wasn't expected
                mismatches.append((index, title_for_report, missed, surprise))

    # Print Summary Output
    print("=" * 40)
    print(f"SUMMARY RESULTS: Passed {passed_count}/{total}")
    print("=" * 40)

    if failures:
        print("\nFailed Cases:")
        for item_num, reasons in failures:
            print(f"\nItem #{item_num}:")
            for reason in reasons:
                print(f"  - {reason}")

    # --- self-check report ---
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

if __name__ == "__main__":
    grade_excerpts()
