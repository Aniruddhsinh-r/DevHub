# Task 8 — Adversarial Excerpt Search

## Goal

The goal was to generate bad article excerpts that can still pass the existing rules and identify weaknesses in the rule-based validator.

The adversarial search checks for:

* Repetition
* Meaningless text
* Padding/filler
* Off-topic content

## What the New Rules Catch

The adversarial search found several bad patterns that could be detected with simple rules.

* **Rule 13:** Catches excerpts with no meaningful vocabulary overlap with the article title or body. This helps detect obvious off-topic or placeholder text.
* **Rule 14:** Catches excerpts dominated by generic filler/clickbait words.
* **Rule 15:** Catches excerpts dominated by repeated word-pairs.

I made my best effort to catch obvious repetitive words, repeated patterns, filler text, and clearly off-topic text using these rules.

## What Rules Cannot Perfectly Catch

Rules **cannot perfectly detect meaningless text or every off-topic excerpt**.

For example, an excerpt may:

* Use words from the actual article but still be meaningless.
* Use correct words but not represent the article's main idea.
* Look grammatically correct but be completely irrelevant to the reader.

These cases require understanding the **meaning and context** of the text, which simple rules cannot reliably determine.

## Honest Limitation

Without a reliable real AI API/model to evaluate semantic meaning, I cannot perfectly check whether every excerpt is meaningful, relevant, or a good summary.

Therefore, the system focuses on what deterministic rules can reasonably detect: obvious repetition, filler, placeholder text, and clear vocabulary mismatch.

Some adversarial examples may therefore remain **"cannot be caught by rules"** because catching them would require semantic understanding rather than another simple rule.

## Conclusion

Task 8 helped identify weaknesses in the original rules and led to Rules 13–15.

The new rules catch many obvious bad patterns, especially repetitive, filler-heavy, and clearly off-topic excerpts. However, complete detection of meaningless or semantically wrong excerpts is outside the reliable capability of rule-based checks alone.
