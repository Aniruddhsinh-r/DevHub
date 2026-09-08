import json
import subprocess
import sys
import os
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
RESULTS_FILE = ROOT / "tests" / "AiTesting" / "results.json"
SUMMARY_FILE = ROOT / "tests" / "AiTesting" / "summary.md"

COMMANDS = [
    # Titles
    [
        "Titles mutation",
        "python", "tests/AiTesting/Titles.py",
        "tests/AiTesting/Titles.json", "--mutation"
    ],
    [
        "Titles metamorphic",
        "python", "tests/AiTesting/Titles.py",
        "tests/AiTesting/Titles.json", "--metamorphic"
    ],
    [
        "Titles normal",
        "python", "tests/AiTesting/Titles.py",
        "tests/AiTesting/Titles.json"
    ],
    [
        "Titles fuzz",
        "python", "tests/AiTesting/Titles.py", "--fuzz", "500"
    ],

    # Articles
    [
        "Articles mutation",
        "python", "tests/AiTesting/Testing.py",
        "tests/AiTesting/Articles.json", "--mutation"
    ],
    [
        "Articles metamorphic",
        "python", "tests/AiTesting/Testing.py",
        "tests/AiTesting/Articles.json", "--metamorphic"
    ],
    [
        "Articles fake-ai",
        "python", "tests/AiTesting/Testing.py",
        "tests/AiTesting/Articles.json", "--fake-ai"
    ],
    [
        "Articles consistency",
        "python", "tests/AiTesting/Testing.py",
        "tests/AiTesting/Articles.json", "--consistency"
    ],
    [
        "Articles consistency 0.8",
        "python", "tests/AiTesting/Testing.py",
        "tests/AiTesting/Articles.json",
        "--consistency", "--min-pass-rate", "0.8"
    ],
    [
        "Articles normal",
        "python", "tests/AiTesting/Testing.py",
        "tests/AiTesting/Articles.json"
    ],
    [
        "Articles fuzz",
        "python", "tests/AiTesting/Testing.py", "--fuzz", "500"
    ],
    [
        "Articles pattern",
        "python", "tests/AiTesting/Testing.py", "--pattern"
    ],

    # Comments
    [
        "Comments mutation",
        "python", "tests/AiTesting/Comments.py",
        "tests/AiTesting/Comments.json", "--mutation"
    ],
    [
        "Comments metamorphic",
        "python", "tests/AiTesting/Comments.py",
        "tests/AiTesting/Comments.json", "--metamorphic"
    ],
    [
        "Comments normal",
        "python", "tests/AiTesting/Comments.py",
        "tests/AiTesting/Comments.json"
    ],
    [
        "Comments fuzz",
        "python", "tests/AiTesting/Comments.py", "--fuzz", "500"
    ],
    
    # Task 6 — HTML comparison
    [
        "HTML comparison",
        "python", "tests/AiTesting/compare.py"
    ],

    # Task 7 — Hypothesis
    [
        "Hypothesis tests",
        "python", "-m", "pytest",
        "tests/AiTesting/hypothesis_test.py", "-q"
    ],

    # Task 8 — Adversarial search
    [
        "Adversarial search",
        "python", "tests/AiTesting/adversarial.py"
    ],
    [
        "Adversarial tests",
        "python", "tests/AiTesting/adversarial_test.py"
    ],
]


def main():
    results = []
    overall_passed = True

    print("=" * 60)
    print("RUNNING ALL AI TESTS")
    print("=" * 60)

    for name, *command in COMMANDS:
        print(f"\n▶ {name}")
        print("  " + " ".join(command))

        env = os.environ.copy()
        env["PYTHONIOENCODING"] = "utf-8"

        result = subprocess.run(
            [sys.executable, *command[1:]],
            cwd=ROOT,
            text=True,
            capture_output=True,
            encoding="utf-8",
            env=env,
        )

        passed = result.returncode == 0
        overall_passed = overall_passed and passed

        results.append({
            "name": name,
            "command": " ".join(command),
            "passed": passed,
            "exit_code": result.returncode,
            "output": result.stdout + result.stderr,
        })

        if passed:
            print("  ✅ PASS")
        else:
            print("  ❌ FAIL")
            print(result.stdout)
            print(result.stderr)

    RESULTS_FILE.write_text(
        json.dumps(
            {
                "passed": overall_passed,
                "total": len(results),
                "passed_count": sum(r["passed"] for r in results),
                "failed_count": sum(not r["passed"] for r in results),
                "results": results,
            },
            indent=2,
        ),
        encoding="utf-8",
    )

    summary = [
        "# AI Testing Summary",
        "",
        f"**Overall:** {'✅ PASS' if overall_passed else '❌ FAIL'}",
        "",
        f"**Total:** {len(results)}",
        f"**Passed:** {sum(r['passed'] for r in results)}",
        f"**Failed:** {sum(not r['passed'] for r in results)}",
        "",
        "| Test | Result | Exit Code |",
        "|---|---|---:|",
    ]

    for result in results:
        status = "✅ PASS" if result["passed"] else "❌ FAIL"
        summary.append(
            f"| {result['name']} | {status} | {result['exit_code']} |"
        )

    SUMMARY_FILE.write_text(
        "\n".join(summary) + "\n",
        encoding="utf-8",
    )

    print("\n" + "=" * 60)
    print(
        "✅ ALL TESTS PASSED"
        if overall_passed
        else "❌ SOME TESTS FAILED"
    )
    print("=" * 60)

    sys.exit(0 if overall_passed else 1)


if __name__ == "__main__":
    main()
