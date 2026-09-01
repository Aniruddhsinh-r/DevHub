import json
import re


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
            # Rule 1: Excerpt not empty (or just whitespace)
            if len(excerpt.strip()) == 0:
                case_failures.append("Rule 1 Failed: Excerpt is empty")

            # Rule 2: Excerpt must have more than 6 characters
            if len(excerpt) <= 6:
                case_failures.append(f"Rule 2 Failed: Length ({len(excerpt)}) is 6 or fewer characters")

            # Rule 3: Excerpt not longer than 50 characters (with spaces)
            if len(excerpt) > 50:
                case_failures.append(f"Rule 3 Failed: Length ({len(excerpt)}) exceeds 50 characters")

            # Rule 4: Must not contain any HTML tags (e.g., <b>, <p>, <br/>)
            if re.search(r"<[^>]+>", excerpt):
                case_failures.append("Rule 4 Failed: Contains forbidden HTML tags")

            # Additional Rule B: Check if excerpt is identical to the full body
            body = item.get("body", "")
            if body and excerpt.strip() == body.strip():
                case_failures.append("Additional Rule Failed: Excerpt is identical to the full body (not a summary)")

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
