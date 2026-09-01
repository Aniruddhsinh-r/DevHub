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

    for index, item in enumerate(cases, start=1):
        excerpt = item.get("excerpt")
        case_failures = []

        # Additional Rule A: Check if excerpt field exists and is a string
        if excerpt is None or not isinstance(excerpt, str):
            case_failures.append("Excerpt field is missing or not a string")
        else:
            if len(excerpt.strip()) == 0:
                case_failures.append("Rule 1 Failed: Excerpt is empty")

            # Rule 2: Excerpt must have more than 6 characters (invisible-char safe)
            cleaned = clean_invisible(excerpt)
            if len(cleaned) <= 6:
                case_failures.append(f"Rule 2 Failed: Length ({len(cleaned)}) is 6 or fewer characters")

            # Rule 3: Excerpt not longer than 50 characters (with spaces)
            if len(excerpt) > 50:
                case_failures.append(f"Rule 3 Failed: Length ({len(excerpt)}) exceeds 50 characters")

            # Rule 4: Must not contain any HTML tags (e.g., <b>, <p>, <br/>)
            if re.search(r"<[^>]+>", excerpt):
                case_failures.append(f"Rule 4 Failed: Contains forbidden HTML tags")

            # Additional Rule B: Check if excerpt is identical to the full body
            body = item.get("body", "")
            if body and excerpt.strip() == body.strip():
                case_failures.append(f"Rule 5 Failed: Excerpt is identical to the full body (not a summary)")

            # Additional Rule C: Excessive invisible-character padding (>30% of raw length)
            if len(excerpt) > 0:
                invisible_count = len(INVISIBLE_CHARS.findall(excerpt))
                if invisible_count / len(excerpt) > 0.3:
                    case_failures.append(
                        f"Rule 6 Failed: Excerpt is mostly invisible characters "
                        f"({invisible_count}/{len(excerpt)})"
                    )

            # Additional Rule D: Contains directional override characters (RTL/LTR spoofing)
            if DIRECTIONAL_OVERRIDES.search(excerpt):
                case_failures.append(f"Rule 7 Failed: Contains RTL/LTR directional override characters")

            # Additional Rule E: Contains raw newlines or tabs (layout-breaking)
            if re.search(r'[\n\r\t]', excerpt):
                case_failures.append(f"Rule 8 Failed: Contains embedded newline/tab characters")

            # Additional Rule F: Contains markdown link/image injection
            if MARKDOWN_INJECTION.search(excerpt):
                case_failures.append(f"Rule 9 Failed: Contains markdown link/image injection")

            # Additional Rule G: Excerpt is emoji-only (no real text content)
            text_no_emoji = EMOJI_PATTERN.sub('', excerpt).strip()
            if excerpt.strip() and not text_no_emoji:
                case_failures.append(f"Rule 10 Failed: Excerpt is emoji-only, no real text")

            # Additional Rule H: Excerpt is just the title repeated (low-effort/SEO stuffing)
            title = item.get("title", "")
            if title and excerpt.strip().lower() == title.strip().lower():
                case_failures.append(f"Rule 11 Failed: Excerpt is identical to the title")

        if not case_failures:
            passed_count += 1
        else:
            failures.append((index, case_failures))

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

if __name__ == "__main__":
    grade_excerpts()
